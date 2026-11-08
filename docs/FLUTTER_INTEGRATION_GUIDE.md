# Flutter Integration Guide & Mobile Client SDK

This guide provides a production-grade, end-to-end blueprint for building high-performance cross-platform mobile applications (**Android & iOS**) in **Flutter (Dart 3+)** powered by the REST API backend.

---

## 1. Architecture & Tech Stack

```text
flutter_app/
├── lib/
│   ├── core/
│   │   ├── constants/api_constants.dart
│   │   └── network/
│   │       ├── api_client.dart
│   │       └── auth_interceptor.dart
│   ├── data/
│   │   ├── models/
│   │   │   ├── app_config.dart
│   │   │   ├── auth_tokens.dart
│   │   │   ├── media_info.dart
│   │   │   └── download_job.dart
│   │   └── repositories/media_repository.dart
│   ├── presentation/
│   │   ├── providers/download_provider.dart
│   │   └── screens/
│   │       ├── home_screen.dart
│   │       └── download_screen.dart
│   └── main.dart
```

### Recommended Dependencies (`pubspec.yaml`)
```yaml
name: zenx_download_app
description: "Universal Media & Audio Downloader powered by Zen X API"
publish_to: "none"
version: 1.0.0+1

environment:
  sdk: ">=3.0.0 <4.0.0"

dependencies:
  flutter:
    sdk: flutter
  flutter_riverpod: ^2.5.1
  dio: ^5.4.3
  path_provider: ^2.1.3
  open_filex: ^4.5.0
  permission_handler: ^11.3.1
  flutter_local_notifications: ^17.1.2
  device_info_plus: ^10.1.0
  shared_preferences: ^2.2.3
  uuid: ^4.4.0
  cached_network_image: ^3.3.1

dev_dependencies:
  flutter_test:
    sdk: flutter
  flutter_lints: ^4.0.0
```

---

## 2. API Constants & Device UUID

Create `lib/core/constants/api_constants.dart`:

```dart
class ApiConstants {
  // Live Gateway URL (Automatically matches server configuration)
  static const String baseUrl = 'http://127.0.0.1:8000/api/v1'; // Replace with your live production URL: https://yourdomain.com/api/v1

  // Client Identifiers
  static const String clientId = 'zenx-android-v1'; // or 'zenx-ios-v1'
  static const String clientSecret = 'zenx_sec_android_prod_9921b7a6e1';

  // Endpoints
  static const String registerDevice = '/auth/register-device';
  static const String refreshToken = '/auth/refresh';
  static const String config = '/config';
  static const String appVersion = '/app/version';
  static const String videoExtract = '/video/extract';
  static const String downloadStart = '/download/start';
  static const String downloadJob = '/download/jobs'; // + /{jobId}
  static const String userHistory = '/user/history';
}
```

---

## 3. Data Models

### 3.1 `MediaInfo` (`lib/data/models/media_info.dart`)

```dart
class MediaInfo {
  final String title;
  final String thumbnail;
  final List<MergedOption> options;
  final List<DirectLink> directLinks;

  MediaInfo({
    required this.title,
    required this.thumbnail,
    required this.options,
    required this.directLinks,
  });

  factory MediaInfo.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>;
    return MediaInfo(
      title: data['title'] ?? 'Media Title',
      thumbnail: data['thumbnail'] ?? '',
      options: (data['merged_options'] as List? ?? [])
          .map((e) => MergedOption.fromJson(e))
          .toList(),
      directLinks: (data['links'] as List? ?? [])
          .map((e) => DirectLink.fromJson(e))
          .toList(),
    );
  }
}

class MergedOption {
  final String label;
  final int height;
  final String type; // 'video' | 'audio'
  final String ext;
  final String qualityBadge;
  final bool isRecommended;

  MergedOption({
    required this.label,
    required this.height,
    required this.type,
    required this.ext,
    required this.qualityBadge,
    required this.isRecommended,
  });

  factory MergedOption.fromJson(Map<String, dynamic> json) {
    return MergedOption(
      label: json['label'] ?? '',
      height: json['height'] ?? 720,
      type: json['type'] ?? 'video',
      ext: json['ext'] ?? 'mp4',
      qualityBadge: json['quality_badge'] ?? 'HD',
      isRecommended: json['is_recommended'] ?? false,
    );
  }
}

class DirectLink {
  final String format;
  final String size;
  final String url;
  final String ext;
  final bool hasVideo;
  final bool hasAudio;

  DirectLink({
    required this.format,
    required this.size,
    required this.url,
    required this.ext,
    required this.hasVideo,
    required this.hasAudio,
  });

  factory DirectLink.fromJson(Map<String, dynamic> json) {
    return DirectLink(
      format: json['format'] ?? '',
      size: json['size'] ?? '',
      url: json['url'] ?? '',
      ext: json['ext'] ?? 'mp4',
      hasVideo: json['has_video'] ?? true,
      hasAudio: json['has_audio'] ?? true,
    );
  }
}
```

### 3.2 `DownloadJob` (`lib/data/models/download_job.dart`)

```dart
class DownloadJob {
  final String jobId;
  final String status; // 'pending' | 'processing' | 'completed' | 'failed'
  final int progressPct;
  final String? streamUrl;
  final String? streamToken;
  final String? filename;
  final String? error;

  DownloadJob({
    required this.jobId,
    required this.status,
    required this.progressPct,
    this.streamUrl,
    this.streamToken,
    this.filename,
    this.error,
  });

  factory DownloadJob.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>;
    return DownloadJob(
      jobId: data['job_id'] ?? '',
      status: data['status'] ?? 'pending',
      progressPct: (data['progress_percentage'] as num?)?.toInt() ?? 0,
      streamUrl: data['stream_url'],
      streamToken: data['stream_token'],
      filename: data['filename'],
      error: data['error'],
    );
  }

  bool get isCompleted => status == 'completed';
  bool get isFailed => status == 'failed';
}
```

---

## 4. Network Client with Token Auto-Refresh

Create `lib/core/network/api_client.dart`:

```dart
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';
import '../constants/api_constants.dart';

class ApiClient {
  late final Dio dio;
  String? _accessToken;
  String? _refreshToken;

  ApiClient() {
    dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          if (_accessToken != null && !options.headers.containsKey('Authorization')) {
            options.headers['Authorization'] = 'Bearer $_accessToken';
          }
          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          // Token expired (HTTP 401) -> Auto-refresh and retry original request
          if (error.response?.statusCode == 401 && _refreshToken != null) {
            try {
              final refreshed = await _refreshAccessToken();
              if (refreshed) {
                final originalRequest = error.requestOptions;
                originalRequest.headers['Authorization'] = 'Bearer $_accessToken';
                final response = await dio.fetch(originalRequest);
                return handler.resolve(response);
              }
            } catch (e) {
              // Refresh failed: Re-register device
              await initializeDevice();
            }
          }
          return handler.next(error);
        },
      ),
    );
  }

  /// Initialize hardware UUID and register device with backend
  Future<void> initializeDevice() async {
    final prefs = await SharedPreferences.getInstance();
    String? deviceUuid = prefs.getString('device_uuid');

    if (deviceUuid == null) {
      deviceUuid = const Uuid().v4();
      await prefs.setString('device_uuid', deviceUuid);
    }

    String deviceModel = 'Mobile Device';
    final deviceInfo = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      final android = await deviceInfo.androidInfo;
      deviceModel = '${android.manufacturer} ${android.model}';
    } else if (Platform.isIOS) {
      final ios = await deviceInfo.iosInfo;
      deviceModel = ios.utsname.machine;
    }

    try {
      final response = await dio.post(
        ApiConstants.registerDevice,
        data: {
          'client_id': ApiConstants.clientId,
          'client_secret': ApiConstants.clientSecret,
          'device_uuid': deviceUuid,
          'platform': Platform.isAndroid ? 'android' : 'ios',
          'device_model': deviceModel,
          'app_version': '1.0.0',
        },
      );

      final tokens = response.data['data']['tokens'];
      _accessToken = tokens['access_token'];
      _refreshToken = tokens['refresh_token'];

      await prefs.setString('access_token', _accessToken!);
      await prefs.setString('refresh_token', _refreshToken!);
    } catch (e) {
      throw Exception('Device registration failed: $e');
    }
  }

  Future<bool> _refreshAccessToken() async {
    final prefs = await SharedPreferences.getInstance();
    final refreshToken = prefs.getString('refresh_token');
    if (refreshToken == null) return false;

    final response = await dio.post(
      ApiConstants.refreshToken,
      data: {'refresh_token': refreshToken},
    );

    if (response.statusCode == 200) {
      final tokens = response.data['data']['tokens'];
      _accessToken = tokens['access_token'];
      _refreshToken = tokens['refresh_token'];
      await prefs.setString('access_token', _accessToken!);
      await prefs.setString('refresh_token', _refreshToken!);
      return true;
    }
    return false;
  }
}
```

---

## 5. Media Repository (`lib/data/repositories/media_repository.dart`)

```dart
import 'dart:async';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/api_client.dart';
import '../models/media_info.dart';
import '../models/download_job.dart';

class MediaRepository {
  final ApiClient apiClient;

  MediaRepository(this.apiClient);

  /// 1. Extract video metadata and available qualities
  Future<MediaInfo> extractMedia(String url) async {
    final response = await apiClient.dio.post(
      ApiConstants.videoExtract,
      data: {'url': url},
    );
    return MediaInfo.fromJson(response.data);
  }

  /// 2. Request backend merging (FFmpeg 8-stream engine)
  Future<String> startDownloadJob({
    required String url,
    required String type, // 'video' or 'audio'
    required int height,  // e.g. 1080, 720
  }) async {
    final response = await apiClient.dio.post(
      ApiConstants.downloadStart,
      data: {
        'url': url,
        'type': type,
        'height': height,
      },
    );
    return response.data['data']['job_id'];
  }

  /// 3. Poll server merge progress until ready
  Future<DownloadJob> pollJobProgress(String jobId, {Function(int pct)? onProgress}) async {
    while (true) {
      final response = await apiClient.dio.get('${ApiConstants.downloadJob}/$jobId');
      final job = DownloadJob.fromJson(response.data);

      if (onProgress != null) {
        onProgress(job.progressPct);
      }

      if (job.isCompleted) {
        return job;
      }
      if (job.isFailed) {
        throw Exception(job.error ?? 'Media merge failed on server.');
      }

      await Future.delayed(const Duration(milliseconds: 1500));
    }
  }

  /// 4. Stream file from server directly to device storage with download progress
  Future<File> downloadFile({
    required String streamUrl,
    required String filename,
    required Function(int received, int total) onReceiveProgress,
  }) async {
    final dir = await getApplicationDocumentsDirectory();
    final savePath = '${dir.path}/$filename';

    await apiClient.dio.download(
      streamUrl,
      savePath,
      onReceiveProgress: onReceiveProgress,
      options: Options(
        responseType: ResponseType.stream,
        followRedirects: true,
      ),
    );

    return File(savePath);
  }
}
```

---

## 6. Riverpod State Management

Create `lib/presentation/providers/download_provider.dart`:

```dart
import 'dart:io';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/network/api_client.dart';
import '../../data/models/media_info.dart';
import '../../data/repositories/media_repository.dart';

final apiClientProvider = Provider<ApiClient>((ref) => ApiClient());

final mediaRepositoryProvider = Provider<MediaRepository>((ref) {
  final client = ref.watch(apiClientProvider);
  return MediaRepository(client);
});

enum DownloadState { initial, extracting, ready, processing, downloading, completed, error }

class DownloadNotifier extends StateNotifier<AsyncValue<MediaInfo?>> {
  final MediaRepository repository;

  DownloadNotifier(this.repository) : super(const AsyncValue.data(null));

  double mergeProgress = 0.0;
  double downloadProgress = 0.0;
  File? downloadedFile;
  String? errorMessage;

  Future<void> extract(String url) async {
    state = const AsyncValue.loading();
    try {
      final info = await repository.extractMedia(url);
      state = AsyncValue.data(info);
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> startDownload(String url, MergedOption option, Function(File) onComplete) async {
    try {
      // Step A: Trigger server merge
      final jobId = await repository.startDownloadJob(
        url: url,
        type: option.type,
        height: option.height,
      );

      // Step B: Poll server progress
      final job = await repository.pollJobProgress(jobId, onProgress: (pct) {
        mergeProgress = pct / 100.0;
      });

      // Step C: Stream file to device
      final file = await repository.downloadFile(
        streamUrl: job.streamUrl!,
        filename: job.filename ?? 'downloaded_media.${option.ext}',
        onReceiveProgress: (received, total) {
          if (total > 0) {
            downloadProgress = received / total;
          }
        },
      );

      downloadedFile = file;
      onComplete(file);
    } catch (e) {
      errorMessage = e.toString();
    }
  }
}

final downloadProvider = StateNotifierProvider<DownloadNotifier, AsyncValue<MediaInfo?>>((ref) {
  final repo = ref.watch(mediaRepositoryProvider);
  return DownloadNotifier(repo);
});
```

---

## 7. Interactive Screen (`lib/presentation/screens/home_screen.dart`)

```dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:open_filex/open_filex.dart';
import '../providers/download_provider.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final TextEditingController _urlController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    final mediaState = ref.watch(downloadProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Media Downloader', style: TextStyle(fontWeight: FontWeight.bold)),
        elevation: 0,
        backgroundColor: Colors.blueAccent,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // URL Input Field
            TextField(
              controller: _urlController,
              decoration: InputDecoration(
                hintText: 'Paste YouTube, Facebook, Twitter URL...',
                prefixIcon: const Icon(Icons.link),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () => _urlController.clear(),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Extract Button
            ElevatedButton.icon(
              onPressed: () {
                final url = _urlController.text.trim();
                if (url.isNotEmpty) {
                  ref.read(downloadProvider.notifier).extract(url);
                }
              },
              icon: const Icon(Icons.cloud_download),
              label: const Text('Fetch Video', style: TextStyle(fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
            const SizedBox(height: 24),

            // Results State
            mediaState.when(
              data: (mediaInfo) {
                if (mediaInfo == null) return const SizedBox();
                return Card(
                  elevation: 3,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: CachedNetworkImage(
                            imageUrl: mediaInfo.thumbnail,
                            height: 180,
                            width: double.infinity,
                            fit: BoxFit.cover,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          mediaInfo.title,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const Divider(height: 24),
                        const Text('Select Quality:', style: TextStyle(fontWeight: FontWeight.bold)),
                        const SizedBox(height: 8),

                        // Quality chips
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: mediaInfo.options.map((opt) {
                            return ActionChip(
                              avatar: Icon(
                                opt.type == 'audio' ? Icons.audiotrack : Icons.movie,
                                size: 16,
                              ),
                              label: Text('${opt.label} (${opt.qualityBadge})'),
                              onPressed: () {
                                ref.read(downloadProvider.notifier).startDownload(
                                  _urlController.text.trim(),
                                  opt,
                                  (savedFile) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text('Downloaded: ${savedFile.path}'),
                                        action: SnackBarAction(
                                          label: 'OPEN',
                                          onPressed: () => OpenFilex.open(savedFile.path),
                                        ),
                                      ),
                                    );
                                  },
                                );
                              },
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  ),
                );
              },
              loading: () => const Center(
                child: Padding(
                  padding: EdgeInsets.all(24),
                  child: CircularProgressIndicator(),
                ),
              ),
              error: (err, _) => Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text('Error: $err', style: TextStyle(color: Colors.red.shade900)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## 8. Android & iOS Permissions Configuration

### Android (`android/app/src/main/AndroidManifest.xml`)
Add inside `<manifest>`:
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE" android:maxSdkVersion="28" />
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
```

### iOS (`ios/Runner/Info.plist`)
Add inside `<dict>`:
```xml
<key>NSAppTransportSecurity</key>
<dict>
    <key>NSAllowsArbitraryLoads</key>
    <true/>
</dict>
<key>UIBackgroundModes</key>
<array>
    <string>fetch</string>
    <string>remote-notification</string>
</array>
```

---

## 9. Launching & Testing

```bash
# 1. Install dependencies
flutter pub get

# 2. Run on connected device or simulator
flutter run
```

