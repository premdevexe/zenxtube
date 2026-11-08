@extends('admin.layout')

@section('title', 'Download Logs & History')
@section('page_title', 'Download Activity Logs')

@section('content')

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.downloads') }}" method="get" class="row align-items-center">
                <div class="col-lg-5 col-md-6 col-12 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control border-left-0" placeholder="Search by video title, URL, or IP..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-3 col-6">
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Formats (Video & Audio)</option>
                        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Video (MP4) Only</option>
                        <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>Audio (MP3) Only</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-3 col-6 text-right">
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    @if(request('search') || request('type'))
                        <a href="{{ route('admin.downloads') }}" class="btn btn-outline-secondary font-weight-bold">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Downloads Table -->
    <div class="card border-0 shadow-sm bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-dark mb-0">
                <i class="fa fa-list text-primary mr-1"></i> Logs (Total: {{ $downloads->total() }})
            </h6>
            @if($downloads->total() > 0)
                <form action="{{ route('admin.downloads.clear') }}" method="post" onsubmit="return confirm('Are you sure you want to permanently clear all download logs?')">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                        <i class="fa fa-trash"></i> Clear Logs History
                    </button>
                </form>
            @endif
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Media Information</th>
                            <th>Format</th>
                            <th>Type</th>
                            <th>IP Address</th>
                            <th>Downloaded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($downloads as $dl)
                            <tr>
                                <td class="align-middle text-muted small font-weight-bold">#{{ $dl->id }}</td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark d-block text-truncate" style="max-width: 450px;" title="{{ $dl->video_title }}">
                                        {{ $dl->video_title ?? 'Video' }}
                                    </span>
                                    <small class="text-muted d-block text-truncate" style="max-width: 450px;">
                                        <a href="{{ $dl->video_url }}" target="_blank" class="text-primary">{{ $dl->video_url }}</a>
                                    </small>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-light border font-weight-bold">{{ $dl->format_label ?? 'Standard' }}</span>
                                </td>
                                <td class="align-middle">
                                    @if($dl->type === 'audio')
                                        <span class="badge badge-warning text-dark font-weight-bold"><i class="fa fa-music"></i> Audio</span>
                                    @else
                                        <span class="badge badge-primary font-weight-bold"><i class="fa fa-video-camera"></i> Video</span>
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    <code>{{ $dl->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td class="align-middle small text-muted">
                                    {{ $dl->created_at ? $dl->created_at->format('M d, Y H:i:s') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                                    <h5>No download logs found</h5>
                                    <p class="small mb-0">Downloads initiated from the front-end will appear here in real-time.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($downloads->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $downloads->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

@endsection

