@extends('app')

@section('title', \App\SiteSetting::get('seo_title', \App\SiteSetting::get('site_name', 'Zen X Download')))

@section('content')

@php
    $tagline = \App\SiteSetting::get('site_tagline', 'Direct stream extraction & conversion');
    $platforms = [
        \App\SiteSetting::get('feat_youtube') === '1'   ? ['label' => 'YouTube', 'icon' => 'fa-youtube-play'] : null,
        \App\SiteSetting::get('feat_facebook') === '1'  ? ['label' => 'Facebook', 'icon' => 'fa-facebook-official'] : null,
        \App\SiteSetting::get('feat_instagram') === '1' ? ['label' => 'Instagram', 'icon' => 'fa-instagram'] : null,
        \App\SiteSetting::get('feat_twitter') === '1'   ? ['label' => 'Twitter / X', 'icon' => 'fa-twitter'] : null,
        \App\SiteSetting::get('feat_vimeo') === '1'     ? ['label' => 'Vimeo', 'icon' => 'fa-vimeo'] : null,
        \App\SiteSetting::get('feat_mp3') === '1'       ? ['label' => 'MP3 Audio', 'icon' => 'fa-music'] : null,
        \App\SiteSetting::get('feat_mp4_4k') === '1'    ? ['label' => '4K / 1080p MP4', 'icon' => 'fa-film'] : null,
    ];
    $platforms = array_filter($platforms);
@endphp

    <!-- Hero Section -->
    <section class="bg-white border-bottom py-5">
        <div class="container text-center py-2 px-3 px-sm-4" style="max-width: 840px;">
            <h1 class="h2 fw-semibold text-dark mb-2" style="letter-spacing: -0.025em;">Online Video and Audio Extraction</h1>
            <p class="text-secondary mx-auto mb-4 small" style="max-width: 620px; line-height: 1.55;">
                Download public media in <strong>4K, 1080p, 720p MP4</strong> or extract original audio tracks to <strong>320kbps MP3</strong> without ads or registration.
            </p>

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mx-auto text-left py-2 px-3 mb-3 small rounded" role="alert" style="max-width: 680px;">
                    <i class="fa fa-exclamation-circle text-danger mr-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form id="fd" action="{{ url('download') }}" method="post" onsubmit="handleSearchSubmit(this)" class="mx-auto p-0" style="max-width: 680px;">
                {{ csrf_field() }}
                <div class="input-group search-studio-group">
                    <input name="videourl" 
                           required 
                           class="form-control font-mono py-2 px-3" 
                           placeholder="Paste video or playlist URL here..." 
                           type="text" 
                           style="border-radius: 8px 0 0 8px; border: 1px solid rgba(17,24,39,0.18); font-size: 0.88rem;"
                           autofocus>
                    <div class="input-group-append">
                        <button id="homeSubmitBtn" class="btn btn-dark px-4" style="border-radius: 0 8px 8px 0; font-size: 0.88rem; font-weight: 500;">
                            <span>Fetch stream</span>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-center flex-wrap align-items-center mt-3 text-muted small font-mono" style="font-size: 0.76rem; gap: 16px;">
                    <span>&bull; No accounts required</span>
                    <span>&bull; Lossless remuxing</span>
                    <span>&bull; 15-minute transient purge</span>
                </div>
            </form>
        </div>
    </section>

    <!-- Platform Compatibility Strip -->
    <div class="bg-light py-3 border-bottom">
        <div class="container text-center px-3">
            <div class="d-flex justify-content-center flex-wrap align-items-center" style="gap: 8px;">
                <span class="text-secondary small font-mono mr-2" style="font-size: 0.74rem;">[SOURCES]</span>
                @foreach($platforms as $p)
                    <span class="badge badge-light border text-secondary font-mono px-2 py-1" style="font-size: 0.75rem; font-weight: 500;">
                        <i class="fa {{ $p['icon'] }} text-muted mr-1"></i> {{ $p['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Extraction Workflow -->
    <section class="py-5 bg-white border-bottom" id="how-to-download">
        <div class="container px-3 px-sm-4" style="max-width: 960px;">
            <div class="mb-4">
                <div class="font-mono text-muted small mb-1" style="font-size: 0.72rem;">[PIPELINE]</div>
                <h2 class="h5 fw-semibold text-dark mb-1">Three-step processing flow</h2>
                <p class="text-secondary small mb-0">How the conversion pipeline handles public stream extraction.</p>
            </div>

            <div class="row g-3">
                <div class="col-md-4 col-12 mb-3 mb-md-0">
                    <div class="p-3 border rounded h-100 bg-white">
                        <div class="font-mono text-muted small mb-2 fw-semibold">01 / LINK</div>
                        <h3 class="h6 fw-semibold text-dark mb-1">Copy source link</h3>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Copy the video or audio URL directly from YouTube, Twitter/X, Facebook, or supported streaming hosts.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12 mb-3 mb-md-0">
                    <div class="p-3 border rounded h-100 bg-white">
                        <div class="font-mono text-muted small mb-2 fw-semibold">02 / FORMAT</div>
                        <h3 class="h6 fw-semibold text-dark mb-1">Select codec &amp; bitrate</h3>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Inspect available media representations: resolutions up to 4K (2160p), 1080p 60fps, or standalone 320kbps MP3 audio.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12">
                    <div class="p-3 border rounded h-100 bg-white">
                        <div class="font-mono text-muted small mb-2 fw-semibold">03 / STREAM</div>
                        <h3 class="h6 fw-semibold text-dark mb-1">Direct file transfer</h3>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Multi-threaded remuxing joins separate video and audio channels without lossy transcode delays.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Architecture & Features -->
    <section class="py-5 bg-light border-bottom" id="features">
        <div class="container px-3 px-sm-4" style="max-width: 960px;">
            <div class="mb-4">
                <div class="font-mono text-muted small mb-1" style="font-size: 0.72rem;">[ARCHITECTURE]</div>
                <h2 class="h5 fw-semibold text-dark mb-1">Engine characteristics</h2>
                <p class="text-secondary small mb-0">High-throughput, privacy-focused media infrastructure specifications.</p>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-12 mb-3">
                    <div class="p-3 bg-white border rounded h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="font-mono text-dark fw-semibold small">01. Multi-threaded fragment fetching</span>
                        </div>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Splits media downloads into 8 concurrent chunk streams via yt-dlp, bypassing single-connection CDN bandwidth throttles.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <div class="p-3 bg-white border rounded h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="font-mono text-dark fw-semibold small">02. Ephemeral storage isolation</span>
                        </div>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            No permanent media retention. Converted files in <code>storage/app/downloads</code> are automatically purged after 15 minutes.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-12 mb-3 mb-md-0">
                    <div class="p-3 bg-white border rounded h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="font-mono text-dark fw-semibold small">03. Lossless audio extraction</span>
                        </div>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Demuxes audio streams directly to MP3 and M4A containers preserving source sample rate and dynamic range.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="p-3 bg-white border rounded h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="font-mono text-dark fw-semibold small">04. 4K high-efficiency muxing</span>
                        </div>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Automated FFmpeg muxing merges isolated adaptive 1080p, 1440p, and 2160p video streams with high-bitrate Opus/AAC audio.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Frequently Asked Questions -->
    <section class="py-5 bg-white" id="faq">
        <div class="container px-3 px-sm-4" style="max-width: 840px;">
            <div class="mb-4">
                <div class="font-mono text-muted small mb-1" style="font-size: 0.72rem;">[DOCUMENTATION]</div>
                <h2 class="h5 fw-semibold text-dark mb-1">Frequently asked questions</h2>
            </div>

            <div class="accordion" id="faqAccordion">
                <div class="card border rounded mb-2">
                    <div class="card-header bg-white py-2 px-3" id="faq1">
                        <button class="btn btn-link text-dark fw-medium text-left d-block w-100 p-0 text-decoration-none small" type="button" data-toggle="collapse" data-target="#collapse1" aria-expanded="true">
                            Is {{ \App\SiteSetting::get('site_name', 'Zen X Download') }} free to use?
                        </button>
                    </div>
                    <div id="collapse1" class="collapse show" data-parent="#faqAccordion">
                        <div class="card-body text-secondary small py-2 px-3 border-top bg-light">
                            Yes. The platform operates without registration walls, subscription requirements, or rate caps on public downloads.
                        </div>
                    </div>
                </div>

                <div class="card border rounded mb-2">
                    <div class="card-header bg-white py-2 px-3" id="faq2">
                        <button class="btn btn-link text-dark fw-medium text-left d-block w-100 p-0 text-decoration-none small collapsed" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="false">
                            Where are downloaded files stored?
                        </button>
                    </div>
                    <div id="collapse2" class="collapse" data-parent="#faqAccordion">
                        <div class="card-body text-secondary small py-2 px-3 border-top bg-light">
                            Files are streamed directly to your browser's designated local download directory.
                        </div>
                    </div>
                </div>

                <div class="card border rounded mb-2">
                    <div class="card-header bg-white py-2 px-3" id="faq3">
                        <button class="btn btn-link text-dark fw-medium text-left d-block w-100 p-0 text-decoration-none small collapsed" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="false">
                            Why are 1080p and 4K streams tagged as "Merged"?
                        </button>
                    </div>
                    <div id="collapse3" class="collapse" data-parent="#faqAccordion">
                        <div class="card-body text-secondary small py-2 px-3 border-top bg-light">
                            YouTube stores 1080p+ video and high-quality audio as separate adaptive streams. Our background engine automatically merges the parallel streams into a single synchronized MP4 container.
                        </div>
                    </div>
                </div>

                <div class="card border rounded">
                    <div class="card-header bg-white py-2 px-3" id="faq4">
                        <button class="btn btn-link text-dark fw-medium text-left d-block w-100 p-0 text-decoration-none small collapsed" type="button" data-toggle="collapse" data-target="#collapse4" aria-expanded="false">
                            What is the retention window for processed files?
                        </button>
                    </div>
                    <div id="collapse4" class="collapse" data-parent="#faqAccordion">
                        <div class="card-body text-secondary small py-2 px-3 border-top bg-light">
                            All merged files are stored ephemerally and automatically purged after 15 minutes by scheduled cleanup tasks.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
<script>
function handleSearchSubmit(form) {
    const btn = document.getElementById('homeSubmitBtn');
    if (btn) {
        btn.innerHTML = '<i class="fa fa-circle-o-notch fa-spin mr-1"></i> Extracting stream...';
        btn.classList.add('disabled');
    }
}

// Reset button state if navigated back via browser history (bfcache)
window.addEventListener('pageshow', function() {
    const btn = document.getElementById('homeSubmitBtn');
    if (btn) {
        btn.innerHTML = '<span>Fetch Video</span> <i class="fa fa-arrow-right" style="font-size: 0.76rem;"></i>';
        btn.classList.remove('disabled');
        btn.removeAttribute('disabled');
    }
});
</script>
@endsection
