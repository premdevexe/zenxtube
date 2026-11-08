@extends('admin.layout')

@section('title', 'System Dashboard')
@section('page_title', 'System telemetry')

@section('content')

    <!-- Top Metric Cards -->
    <div class="row mb-4">
        <!-- Total Downloads -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border rounded-2 p-3 h-100 bg-white">
                <div class="text-secondary small font-mono" style="font-size: 0.74rem;">[METRIC: COMPLETED_DOWNLOADS]</div>
                <div class="h3 font-mono fw-semibold text-dark mb-1 mt-2">{{ number_format($totalDownloads) }}</div>
                <div class="small text-secondary font-mono" style="font-size: 0.76rem;">
                    <span class="text-dark fw-medium">+{{ number_format($todayDownloads) }}</span> vs today
                </div>
            </div>
        </div>

        <!-- Video vs Audio -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border rounded-2 p-3 h-100 bg-white">
                <div class="text-secondary small font-mono" style="font-size: 0.74rem;">[RATIO: MP4 / MP3]</div>
                <div class="h3 font-mono fw-semibold text-dark mb-1 mt-2">
                    {{ number_format($videoDownloads) }} <span class="text-muted" style="font-size: 1.1rem;">/</span> {{ number_format($audioDownloads) }}
                </div>
                <div class="small text-secondary font-mono" style="font-size: 0.76rem;">
                    Video vs audio extraction
                </div>
            </div>
        </div>

        <!-- Inquiries / Messages -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border rounded-2 p-3 h-100 bg-white">
                <div class="text-secondary small font-mono" style="font-size: 0.74rem;">[QUEUE: INBOX_INQUIRIES]</div>
                <div class="h3 font-mono fw-semibold text-dark mb-1 mt-2">{{ number_format($totalMessages) }}</div>
                <div class="small text-secondary font-mono" style="font-size: 0.76rem;">
                    @if($unreadMessages > 0)
                        <span class="text-danger fw-medium">{{ $unreadMessages }} unread</span> pending review
                    @else
                        <span>0 unread in queue</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Server Temp Disk Usage -->
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card border rounded-2 p-3 h-100 bg-white">
                <div class="text-secondary small font-mono" style="font-size: 0.74rem;">[VOL: EPHEMERAL_STORAGE]</div>
                <div class="h3 font-mono fw-semibold text-dark mb-1 mt-2">{{ $storageSizeFormatted }}</div>
                <div class="small text-secondary font-mono" style="font-size: 0.76rem;">
                    {{ $tempFileCount }} cached &bull; 15m auto-prune
                </div>
            </div>
        </div>
    </div>

    <!-- Engine Telemetry & Maintenance Controls -->
    <div class="card border rounded-2 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="fw-semibold small text-dark mb-1 font-mono">[ENGINE DIAGNOSTICS]</div>
                    <div class="d-flex flex-wrap text-secondary small font-mono" style="font-size: 0.76rem; gap: 14px;">
                        <span>yt-dlp: v{{ $ytdlpVersion }}</span>
                        <span>FFmpeg: {{ $ffmpegStatus }}</span>
                        <span>PHP: {{ PHP_VERSION }}</span>
                        <span>Database: MySQL/MariaDB</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form action="{{ route('admin.system.clear-storage') }}" method="post" class="d-inline" onsubmit="return confirm('Purge all temporary files in storage/app/downloads?')">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-sm btn-outline-dark font-mono" style="font-size: 0.78rem;">
                            <span>Purge ephemeral storage</span>
                        </button>
                    </form>
                    <form action="{{ route('admin.system.clear-cache') }}" method="post" class="d-inline">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-sm btn-dark font-mono" style="font-size: 0.78rem;">
                            <span>Flush cache</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables: Recent Downloads & Inquiries -->
    <div class="row">
        <!-- Recent Downloads -->
        <div class="col-lg-8 col-12 mb-4">
            <div class="card border rounded-2 bg-white h-100">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center font-mono" style="font-size: 0.78rem;">
                    <span class="fw-semibold text-dark">RECENT EXTRACTION LOG</span>
                    <a href="{{ route('admin.downloads') }}" class="text-secondary text-decoration-none">View all &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.82rem;">
                            <thead class="table-light font-mono" style="font-size: 0.74rem;">
                                <tr>
                                    <th class="ps-3" style="width: 45%;">Media source</th>
                                    <th style="width: 15%;">Format</th>
                                    <th style="width: 18%;">Client IP</th>
                                    <th class="text-end pe-3" style="width: 22%;">Action / Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDownloads as $dl)
                                    <tr>
                                        <td class="ps-3 py-2">
                                            <span class="fw-medium text-dark d-block text-truncate" style="max-width: 320px;" title="{{ $dl->video_title }}">
                                                {{ $dl->video_title ?? 'Stream Capture' }}
                                            </span>
                                            <span class="text-muted text-truncate font-mono d-block" style="max-width: 320px; font-size: 0.74rem;">
                                                <a href="{{ $dl->video_url }}" target="_blank" class="text-muted text-decoration-none">{{ $dl->video_url }}</a>
                                            </span>
                                        </td>
                                        <td class="font-mono">
                                            @if($dl->type === 'audio')
                                                <span class="badge badge-light border text-secondary">MP3</span>
                                            @else
                                                <span class="badge badge-light border text-dark">{{ $dl->format_label ?? 'MP4' }}</span>
                                            @endif
                                        </td>
                                        <td class="font-mono text-muted" style="font-size: 0.76rem;">
                                            {{ $dl->ip_address ?? '127.0.0.1' }}
                                        </td>
                                        <td class="text-end pe-3 font-mono" style="white-space: nowrap;">
                                            <span class="text-muted small mr-2" style="font-size: 0.72rem;">{{ $dl->created_at ? $dl->created_at->diffForHumans(null, true) : 'now' }}</span>
                                            @if(!empty($dl->video_url))
                                                <form action="{{ url('download') }}" method="post" target="_blank" class="d-inline">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="videourl" value="{{ $dl->video_url }}">
                                                    <button type="submit" class="btn btn-outline-primary py-0 px-2 font-mono font-weight-bold" style="font-size: 0.72rem; border-radius: 4px;" title="Fetch &amp; Download in new tab">
                                                        <i class="fa fa-cloud-download"></i> Fetch
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted font-mono" style="font-size: 0.78rem;">
                                            [LOG_EMPTY] No extractions recorded yet.
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
            <div class="card border rounded-2 bg-white h-100">
                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center font-mono" style="font-size: 0.78rem;">
                    <span class="fw-semibold text-dark">INQUIRY QUEUE</span>
                    <a href="{{ route('admin.messages') }}" class="text-secondary text-decoration-none">Inbox &rarr;</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size: 0.82rem;">
                        @forelse($recentMessages as $msg)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-medium text-dark small">{{ $msg->name }}</span>
                                    <span class="font-mono" style="font-size: 0.72rem;">
                                        @if($msg->status === 'unread')
                                            <span class="text-danger fw-semibold">[UNREAD]</span>
                                        @else
                                            <span class="text-muted">[READ]</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="text-secondary small mb-1">{{ $msg->subject }}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.76rem;">{{ $msg->message }}</div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted font-mono" style="font-size: 0.78rem;">
                                [INBOX_EMPTY] No pending inquiries.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
