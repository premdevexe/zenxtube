@extends('app')

@section('title', \App\SiteSetting::get('seo_title', \App\SiteSetting::get('site_name', 'Zen X Download')))

@section('content')

@php
    $tagline = \App\SiteSetting::get('site_tagline', 'Ultra-Fast Engine & Zero Lag');
    $platforms = [
        \App\SiteSetting::get('feat_youtube') === '1'   ? ['label' => 'YouTube', 'icon' => 'fa-youtube-play', 'color' => 'text-danger', 'i' => 1] : null,
        \App\SiteSetting::get('feat_facebook') === '1'  ? ['label' => 'Facebook', 'icon' => 'fa-facebook-official', 'color' => 'text-primary', 'i' => 1] : null,
        \App\SiteSetting::get('feat_instagram') === '1' ? ['label' => 'Instagram', 'icon' => 'fa-instagram', 'color' => 'text-danger', 'i' => 1] : null,
        \App\SiteSetting::get('feat_twitter') === '1'   ? ['label' => 'Twitter / X', 'icon' => 'fa-twitter', 'color' => 'text-info', 'i' => 1] : null,
        \App\SiteSetting::get('feat_vimeo') === '1'     ? ['label' => 'Vimeo', 'icon' => 'fa-vimeo', 'color' => 'text-info', 'i' => 1] : null,
        \App\SiteSetting::get('feat_mp3') === '1'       ? ['label' => 'MP3 Audio', 'icon' => 'fa-music', 'color' => 'text-warning', 'i' => 1] : null,
        \App\SiteSetting::get('feat_mp4_4k') === '1'    ? ['label' => '1080p / 4K MP4', 'icon' => 'fa-film', 'color' => 'text-success', 'i' => 1] : null,
    ];
    $platforms = array_filter($platforms);
@endphp

    <!-- Hero Section -->
    <div class="jumbotron jumbotron-fluid bg-white border-bottom mb-0 py-4 py-md-5" style="box-shadow: inset 0 -10px 20px -10px rgba(0,0,0,0.03);">
        <div class="container text-center py-2 py-md-4 px-3 px-sm-4">
            <span class="badge badge-primary px-3 py-2 text-uppercase mb-3 font-weight-bold" style="letter-spacing: 0.5px; border-radius: 20px; font-size: 0.78rem;">
                <i class="fa fa-bolt"></i> {{ $tagline }}
            </span>
            <h1 class="hero-title text-dark mb-2">Online Video & Audio Downloader</h1>
            <p class="hero-lead mx-auto mb-4" style="max-width: 680px;">
                Download high-quality videos in <strong>4K, 1080p, 720p MP4</strong> and convert to <strong>High-Quality MP3</strong> from YouTube, Facebook, Twitter, and other public sites.
            </p>

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger col-md-9 col-lg-8 mx-auto text-center shadow-sm" role="alert">
                    <i class="fa fa-exclamation-triangle"></i> {{ $errors->first() }}
                </div>
            @endif

            <form id="fd" action="{{ url('download') }}" method="post" onsubmit="handleSearchSubmit(this)" class="col-md-10 col-lg-8 mx-auto p-0">
                {{ csrf_field() }}
                <div class="input-group responsive-search-box shadow-sm">
                    <input name="videourl" 
                           required 
                           class="form-control responsive-search-input px-3 px-sm-4" 
                           placeholder="Paste YouTube, Facebook, or video link here..." 
                           type="text" 
                           autofocus>
                    <div class="input-group-append">
                        <button id="homeSubmitBtn" class="btn btn-primary responsive-search-btn">
                            <i class="fa fa-cloud-download mr-1" aria-hidden="true"></i> Download
                        </button>
                    </div>
                </div>
                <div class="d-flex justify-content-center flex-wrap mt-3 text-muted small">
                    <span class="mx-2 mb-1"><i class="fa fa-check-circle text-success"></i> No registration</span>
                    <span class="mx-2 mb-1"><i class="fa fa-check-circle text-success"></i> 100% Free & Safe</span>
                    <span class="mx-2 mb-1"><i class="fa fa-check-circle text-success"></i> 8-Thread Speed</span>
                    <span class="mx-2 mb-1"><i class="fa fa-check-circle text-success"></i> Unlimited</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Supported Platforms Grid -->
    <div class="bg-light py-3 py-md-4 border-bottom">
        <div class="container text-center px-3">
            <span class="text-uppercase small font-weight-bold text-muted d-block mb-3" style="letter-spacing: 1px; font-size: 0.75rem;">Supported Platforms & Formats</span>
            <div class="d-flex justify-content-center flex-wrap align-items-center">
                @foreach($platforms as $p)
                    <span class="badge badge-white border px-3 py-2 m-1 font-weight-bold text-dark shadow-sm">
                        <i class="fa {{ $p['icon'] }} {{ $p['color'] }} mr-1"></i> {{ $p['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <section class="py-5 bg-white border-bottom" id="how-to-download">
        <div class="container px-3 px-sm-4">
            <div class="text-center mb-4 mb-md-5">
                <span class="badge badge-info px-3 py-1 text-uppercase font-weight-bold mb-2" style="font-size: 0.75rem;">Simple 3-Step Process</span>
                <h2 class="h3 font-weight-bold text-dark">How to Download Videos in Seconds</h2>
                <p class="text-muted small">Save your favorite videos and audio tracks with just three easy steps.</p>
            </div>

            <div class="row text-center">
                <div class="col-md-4 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4 text-center">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(0, 98, 224, 0.1); color: #0062E0; font-size: 24px; font-weight: bold;">
                            1
                        </div>
                        <h5 class="font-weight-bold text-dark">Copy Video URL</h5>
                        <p class="text-secondary small mb-0">
                            Navigate to YouTube, Facebook, or your preferred platform and copy the link of the video you wish to download.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4 text-center">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(40, 167, 69, 0.1); color: #28a745; font-size: 24px; font-weight: bold;">
                            2
                        </div>
                        <h5 class="font-weight-bold text-dark">Paste & Choose Format</h5>
                        <p class="text-secondary small mb-0">
                            Paste the link into the search bar above and click Download. Select your desired resolution (4K, 1080p, 720p, or MP3 audio).
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4 text-center">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255, 193, 7, 0.15); color: #d39e00; font-size: 24px; font-weight: bold;">
                            3
                        </div>
                        <h5 class="font-weight-bold text-dark">Download Instantly</h5>
                        <p class="text-secondary small mb-0">
                            Our high-speed 8-thread engine merges audio and video streams and initiates your file download right in your browser.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section class="py-5 bg-light border-bottom" id="features">
        <div class="container px-3 px-sm-4">
            <div class="text-center mb-4 mb-md-5">
                <span class="badge badge-primary px-3 py-1 text-uppercase font-weight-bold mb-2" style="font-size: 0.75rem;">Why Zen X Download</span>
                <h2 class="h3 font-weight-bold text-dark">Built for Maximum Speed & Quality</h2>
                <p class="text-muted small">Experience fast, clean video conversion with zero compromise on privacy.</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-primary"><i class="fa fa-bolt fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">High-Speed Multi-Threading</h5>
                        <p class="text-secondary small mb-0">
                            Utilizes 8 concurrent fragment connections and direct stream remuxing to deliver downloads at your maximum internet connection speed.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-success"><i class="fa fa-shield fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">100% Privacy Protected</h5>
                        <p class="text-secondary small mb-0">
                            No account required, no file retention, and no tracking cookies. All processing is transient, safe, and protected by SSL encryption.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-warning"><i class="fa fa-headphones fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">Lossless Audio Extraction</h5>
                        <p class="text-secondary small mb-0">
                            Extract crystal-clear MP3 audio directly from music videos and podcasts with 4-core multi-threaded audio processing.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-info"><i class="fa fa-television fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">Full HD & 4K Ready</h5>
                        <p class="text-secondary small mb-0">
                            Full support for 1080p, 1440p, and 2160p (4K) videos with synchronized sound included through high-speed FFmpeg muxing.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-danger"><i class="fa fa-mobile fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">Mobile & Desktop Friendly</h5>
                        <p class="text-secondary small mb-0">
                            Works smoothly on iOS, Android, macOS, Windows, Linux, and all major web browsers without requiring browser extensions.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="mb-3 text-secondary"><i class="fa fa-list-alt fa-2x"></i></div>
                        <h5 class="font-weight-bold text-dark">Playlist Support</h5>
                        <p class="text-secondary small mb-0">
                            Paste public playlist links to easily browse and select individual videos for rapid batch downloading.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-white" id="faq">
        <div class="container px-3 px-sm-4">
            <div class="text-center mb-4 mb-md-5">
                <span class="badge badge-secondary px-3 py-1 text-uppercase font-weight-bold mb-2" style="font-size: 0.75rem;">Common Questions</span>
                <h2 class="h3 font-weight-bold text-dark">Frequently Asked Questions</h2>
                <p class="text-muted small">Everything you need to know about Zen X Download.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-12">
                    <div class="accordion" id="faqAccordion">
                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header bg-white py-3" id="faq1">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark font-weight-bold text-left d-block w-100 p-0 text-decoration-none" type="button" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                        <i class="fa fa-question-circle text-primary mr-2"></i> Is Zen X Download completely free to use?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse1" class="collapse show" aria-labelledby="faq1" data-parent="#faqAccordion">
                                <div class="card-body text-secondary small">
                                    Yes! Zen X Download is 100% free with unlimited downloads. There are no subscription fees, registration walls, or hidden limits.
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header bg-white py-3" id="faq2">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark font-weight-bold text-left d-block w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                        <i class="fa fa-question-circle text-primary mr-2"></i> Where are my downloaded files saved?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
                                <div class="card-body text-secondary small">
                                    Downloaded files are saved directly into your device's default "Downloads" folder, or the custom folder designated in your web browser preferences.
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header bg-white py-3" id="faq3">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark font-weight-bold text-left d-block w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                        <i class="fa fa-question-circle text-primary mr-2"></i> Why do some 1080p and 4K videos say "Merged Video + Audio"?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
                                <div class="card-body text-secondary small">
                                    YouTube stores high-definition video (1080p, 1440p, 4K) as separate video and audio streams. Our high-speed engine automatically downloads both parallel tracks and merges them using FFmpeg into a single complete MP4 with full sound.
                                </div>
                            </div>
                        </div>

                        <div class="card border mb-2 shadow-sm">
                            <div class="card-header bg-white py-3" id="faq4">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark font-weight-bold text-left d-block w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                        <i class="fa fa-question-circle text-primary mr-2"></i> Is it legal to download videos?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
                                <div class="card-body text-secondary small">
                                    Downloading videos for personal fair-use archive and offline educational viewing is standard practice. Please review our <a href="{{ url('terms-of-service') }}">Terms of Service</a> and ensure you respect the intellectual property rights of content creators.
                                </div>
                            </div>
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
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fetching Video...';
        btn.classList.add('disabled');
    }
}
</script>
@endsection
