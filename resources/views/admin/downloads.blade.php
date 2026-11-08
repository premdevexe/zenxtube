@extends('admin.layout')

@section('title', 'Download Logs & History')
@section('page_title', 'Download Activity Logs')

@section('content')

    <!-- Quick Admin Fetch & Download Bar -->
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <h6 class="font-weight-bold text-dark mb-0 font-mono small">
                <i class="fa fa-cloud-download text-primary mr-1"></i> [ADMIN: DIRECT_MEDIA_FETCH]
            </h6>
        </div>
        <div class="card-body p-3">
            <form action="{{ url('download') }}" method="post" target="_blank" class="row align-items-center">
                {{ csrf_field() }}
                <div class="col-lg-9 col-md-8 col-12 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fa fa-link text-muted"></i></span>
                        </div>
                        <input type="url" name="videourl" class="form-control" placeholder="Paste video link (YouTube, Facebook, Twitter, Vimeo) to fetch &amp; download directly..." required>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-12">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                        <i class="fa fa-cloud-download mr-1"></i> Fetch &amp; Download
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                            <th class="text-right" style="width: 175px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($downloads as $dl)
                            <tr>
                                <td class="align-middle text-muted small font-weight-bold">#{{ $dl->id }}</td>
                                <td class="align-middle">
                                    <span class="font-weight-bold text-dark d-block text-truncate" style="max-width: 380px;" title="{{ $dl->video_title }}">
                                        {{ $dl->video_title ?? 'Video' }}
                                    </span>
                                    <small class="text-muted d-block text-truncate" style="max-width: 380px;">
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
                                <td class="align-middle small font-mono">
                                    <code>{{ $dl->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                                <td class="align-middle small text-muted font-mono" style="white-space: nowrap;">
                                    {{ $dl->created_at ? $dl->created_at->format('M d, Y H:i') : 'N/A' }}
                                </td>
                                <td class="align-middle text-right" style="white-space: nowrap;">
                                    @if(!empty($dl->video_url))
                                        <form action="{{ url('download') }}" method="post" target="_blank" class="d-inline mr-1">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="videourl" value="{{ $dl->video_url }}">
                                            <button type="submit" class="btn btn-sm btn-primary font-weight-bold py-1 px-2" title="Fetch &amp; Download Media">
                                                <i class="fa fa-cloud-download mr-1"></i> Fetch
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 mr-1" onclick="copyLogUrl('{{ addslashes($dl->video_url) }}', this)" title="Copy Link">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('admin.downloads.delete', $dl->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this download log entry?')">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete log">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
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

@section('scripts')
<script>
function copyLogUrl(url, btn) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(function() {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-check text-success"></i>';
            setTimeout(function() { btn.innerHTML = orig; }, 1800);
        });
    } else {
        var el = document.createElement('textarea');
        el.value = url;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check text-success"></i>';
        setTimeout(function() { btn.innerHTML = orig; }, 1800);
    }
}
</script>
@endsection

