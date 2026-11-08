@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')

    <!-- Top KPI Stat Cards -->
    <div class="row mb-4">
        <!-- Total Downloads -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Downloads</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($totalDownloads) }}</h3>
                        <small class="text-success"><i class="fa fa-arrow-up"></i> {{ $todayDownloads }} today</small>
                    </div>
                    <div class="stat-icon bg-light text-primary">
                        <i class="fa fa-cloud-download"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video vs Audio -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Media Breakdown</span>
                        <div class="mt-1">
                            <span class="font-weight-bold text-dark h5 mb-0">{{ $videoDownloads }}</span> <small class="text-muted mr-2">MP4</small>
                            <span class="font-weight-bold text-warning h5 mb-0">{{ $audioDownloads }}</span> <small class="text-muted">MP3</small>
                        </div>
                        <small class="text-muted">Multi-threaded engine</small>
                    </div>
                    <div class="stat-icon bg-light text-warning">
                        <i class="fa fa-film"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inquiries / Messages -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Contact Inquiries</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ number_format($totalMessages) }}</h3>
                        @if($unreadMessages > 0)
                            <span class="badge badge-danger">{{ $unreadMessages }} unread</span>
                        @else
                            <small class="text-muted">All inquiries handled</small>
                        @endif
                    </div>
                    <div class="stat-icon bg-light text-info">
                        <i class="fa fa-envelope-o"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Server Temp Disk Usage -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Storage Cache</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $storageSizeFormatted }}</h3>
                        <small class="text-muted">{{ $tempFileCount }} cached files</small>
                    </div>
                    <div class="stat-icon bg-light text-success">
                        <i class="fa fa-hdd-o"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Tools & Engine Banner -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center">
                <div class="col-lg-7 col-12 mb-3 mb-lg-0">
                    <h6 class="font-weight-bold text-dark mb-1"><i class="fa fa-bolt text-warning mr-1"></i> Engine Status & Diagnostics</h6>
                    <div class="d-flex flex-wrap text-muted small mt-2">
                        <span class="mr-3 mb-1"><i class="fa fa-check-circle text-success"></i> <strong>yt-dlp:</strong> v{{ $ytdlpVersion }}</span>
                        <span class="mr-3 mb-1"><i class="fa fa-check-circle text-success"></i> <strong>FFmpeg:</strong> {{ $ffmpegStatus }}</span>
                        <span class="mr-3 mb-1"><i class="fa fa-server text-primary"></i> <strong>PHP:</strong> v{{ PHP_VERSION }}</span>
                        <span class="mb-1"><i class="fa fa-database text-info"></i> <strong>MySQL:</strong> Connected</span>
                    </div>
                </div>
                <div class="col-lg-5 col-12 text-lg-right">
                    <form action="{{ route('admin.system.clear-storage') }}" method="post" class="d-inline" onsubmit="return confirm('Clean all temporary files in storage/app/downloads?')">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold mr-1">
                            <i class="fa fa-trash"></i> Clean Storage
                        </button>
                    </form>
                    <form action="{{ route('admin.system.clear-cache') }}" method="post" class="d-inline">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-outline-primary btn-sm font-weight-bold">
                            <i class="fa fa-refresh"></i> Clear Cache
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables: Recent Downloads & Inquiries -->
    <div class="row">
        <!-- Recent Downloads -->
        <div class="col-lg-8 col-12 mb-4">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-download text-success mr-1"></i> Recent Downloads</h6>
                    <a href="{{ route('admin.downloads') }}" class="btn btn-sm btn-light font-weight-bold text-primary">View All &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Video / Audio Title</th>
                                    <th>Format</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDownloads as $dl)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="font-weight-bold text-dark d-block text-truncate" style="max-width: 300px;" title="{{ $dl->video_title }}">
                                                {{ $dl->video_title ?? 'Downloaded Media' }}
                                            </span>
                                            <small class="text-muted text-truncate d-block" style="max-width: 300px;">
                                                <a href="{{ $dl->video_url }}" target="_blank" class="text-muted">{{ $dl->video_url }}</a>
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            @if($dl->type === 'audio')
                                                <span class="badge badge-warning text-dark font-weight-bold"><i class="fa fa-music"></i> MP3</span>
                                            @else
                                                <span class="badge badge-primary font-weight-bold"><i class="fa fa-video-camera"></i> {{ $dl->format_label ?? 'MP4' }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle small text-muted">
                                            <code>{{ $dl->ip_address ?? '127.0.0.1' }}</code>
                                        </td>
                                        <td class="align-middle small text-muted">
                                            {{ $dl->created_at ? $dl->created_at->diffForHumans() : 'Just now' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                            No downloads logged yet. Test downloading a video on the homepage!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="col-lg-4 col-12 mb-4">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-envelope text-warning mr-1"></i> Recent Inquiries</h6>
                    <a href="{{ route('admin.messages') }}" class="btn btn-sm btn-light font-weight-bold text-primary">View All &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentMessages as $msg)
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-dark small">{{ $msg->name }}</strong>
                                    <span class="badge {{ $msg->status === 'unread' ? 'badge-danger' : 'badge-light border' }} badge-status">
                                        {{ ucfirst($msg->status) }}
                                    </span>
                                </div>
                                <p class="text-secondary small mb-1 font-weight-bold">{{ $msg->subject }}</p>
                                <p class="text-muted small mb-1 text-truncate">{{ $msg->message }}</p>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $msg->created_at ? $msg->created_at->diffForHumans() : '' }}</small>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fa fa-comments-o fa-2x mb-2 d-block"></i>
                                No inquiries received yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

