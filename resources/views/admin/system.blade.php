@extends('admin.layout')

@section('title', 'System & Diagnostics')
@section('page_title', 'System Health & Maintenance')

@section('content')

    <div class="row">
        <!-- System Specifications -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-server text-primary mr-1"></i> Environment & Server Details</h6>
                    <a href="{{ route('install') }}" target="_blank" class="btn btn-outline-primary btn-sm font-weight-bold">
                        <i class="fa fa-book mr-1"></i> Installation Guide &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0 small">
                        <tbody>
                            <tr>
                                <th style="width: 40%;">PHP Version</th>
                                <td><code>{{ $systemInfo['php_version'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Laravel Version</th>
                                <td><code>{{ $systemInfo['laravel_version'] }}</code></td>
                            </tr>
                            <tr>
                                <th>Operating System</th>
                                <td>{{ $systemInfo['os'] }}</td>
                            </tr>
                            <tr>
                                <th>Web Server</th>
                                <td>{{ $systemInfo['server_software'] }}</td>
                            </tr>
                            <tr>
                                <th>Max Execution Time</th>
                                <td>{{ $systemInfo['max_execution_time'] }}</td>
                            </tr>
                            <tr>
                                <th>Memory Limit</th>
                                <td>{{ $systemInfo['memory_limit'] }}</td>
                            </tr>
                            <tr>
                                <th>Upload Max Filesize</th>
                                <td>{{ $systemInfo['upload_max_filesize'] }}</td>
                            </tr>
                            <tr>
                                <th>yt-dlp Binary</th>
                                <td><code class="text-success">{{ $systemInfo['ytdlp_path'] }}</code></td>
                            </tr>
                            <tr>
                                <th>FFmpeg Binary</th>
                                <td><code class="text-success">{{ $systemInfo['ffmpeg_path'] }}</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <form action="{{ route('admin.system.clear-cache') }}" method="post" class="d-inline mb-2 mb-sm-0">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-outline-primary btn-sm font-weight-bold">
                            <i class="fa fa-refresh"></i> Flush Laravel Cache & Views
                        </button>
                    </form>
                    <form action="{{ route('admin.system.purge-dummy-data') }}" method="post" class="d-inline" onsubmit="return confirm('Purge all test/dummy data (devices, tokens, telemetry logs, test clients)?')">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                            <i class="fa fa-trash-o"></i> Purge Test & Dummy Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Temporary Storage Manager -->
        <div class="col-lg-6 col-12 mb-4">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-2 mb-sm-0">
                        <h6 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-hdd-o text-warning mr-1"></i> Temporary Download Storage
                            <span class="badge badge-success ml-1 font-weight-normal" style="font-size: 0.72rem;"><i class="fa fa-clock-o"></i> 15m Auto-Purge</span>
                        </h6>
                        <small class="text-muted">Storage: <strong>{{ $totalStorageMB }} MB</strong> across {{ count($files) }} files</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <form action="{{ route('admin.system.prune-storage') }}" method="post" class="mr-2">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-outline-warning btn-sm font-weight-bold" title="Delete files older than 15 minutes">
                                <i class="fa fa-clock-o"></i> Prune > 15m
                            </button>
                        </form>
                        @if(count($files) > 0)
                            <form action="{{ route('admin.system.clear-storage') }}" method="post" onsubmit="return confirm('Delete all temporary downloaded media files from storage/app/downloads?')">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                                    <i class="fa fa-trash"></i> Clean All
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover table-sm mb-0 small">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th>File Hash / Name</th>
                                    <th>Size</th>
                                    <th>Age</th>
                                    <th class="text-right">Expires In</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($files as $f)
                                    <tr>
                                        <td class="align-middle">
                                            <code class="text-truncate d-inline-block" style="max-width: 180px;">{{ $f['name'] }}</code>
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $f['size'] }}</td>
                                        <td class="align-middle text-muted">{{ $f['age_minutes'] }} min</td>
                                        <td class="align-middle text-right">
                                            @if($f['remaining_minutes'] > 0)
                                                <span class="badge badge-light border text-muted"><i class="fa fa-hourglass-half text-warning"></i> {{ $f['remaining_minutes'] }}m</span>
                                            @else
                                                <span class="badge badge-danger">Due for delete</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fa fa-check-circle-o fa-2x text-success mb-2 d-block"></i>
                                            Storage directory is clean. No temporary files stored.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light border-top py-2 small text-muted">
                    <i class="fa fa-info-circle text-info"></i> Files stored in <code>storage/app/downloads</code> are automatically deleted after <strong>15 minutes</strong> to keep server storage clean.
                </div>
            </div>
        </div>
    </div>

@endsection

