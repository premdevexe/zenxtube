@php
    $supportEmail = \App\SiteSetting::get('support_email', '');
    $contactLabel = \App\SiteSetting::get('contact_label', 'Contact Us');
    $socials = array_filter([
        \App\SiteSetting::get('social_facebook')  ? ['url' => \App\SiteSetting::get('social_facebook'), 'icon' => 'fa-facebook-official', 'label' => 'Facebook'] : null,
        \App\SiteSetting::get('social_twitter')   ? ['url' => \App\SiteSetting::get('social_twitter'), 'icon' => 'fa-twitter', 'label' => 'Twitter / X'] : null,
        \App\SiteSetting::get('social_instagram') ? ['url' => \App\SiteSetting::get('social_instagram'), 'icon' => 'fa-instagram', 'label' => 'Instagram'] : null,
        \App\SiteSetting::get('social_youtube')   ? ['url' => \App\SiteSetting::get('social_youtube'), 'icon' => 'fa-youtube-play', 'label' => 'YouTube'] : null,
    ]);
@endphp

<footer class="site-footer bg-dark text-light pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6 col-12 mb-4 text-center text-md-left">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                    <img src="{{ \App\SiteSetting::assetPath('site_logo_white', 'images/logo-white.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }} - Fast & Free Media Engine" height="42" class="d-inline-block" style="max-height: 42px; width: auto; object-fit: contain;">
                </div>
                <p class="text-muted small">
                    Zen X Download is an ultra-fast, free online media downloader allowing you to save high-definition videos and audio from YouTube, Facebook, Twitter, and other public platforms in MP4, MP3, 1080p, and 4K without installing software.
                </p>
                <div class="mt-3 d-flex flex-wrap justify-content-center justify-content-md-start">
                    <span class="badge badge-success px-2 py-1 mr-1 mb-1"><i class="fa fa-lock"></i> SSL Encrypted</span>
                    <span class="badge badge-info px-2 py-1 mr-1 mb-1"><i class="fa fa-bolt"></i> 8-Stream Engine</span>
                    <span class="badge badge-primary px-2 py-1 mb-1"><i class="fa fa-check"></i> 100% Free</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6 mb-4 text-center text-md-left">
                <h6 class="text-uppercase text-white font-weight-bold mb-3 border-bottom pb-2 border-secondary">Navigation</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-angle-right mr-1"></i> Home</a></li>
                    <li class="mb-2"><a href="{{ route('how-to-download') }}" class="text-muted"><i class="fa fa-angle-right mr-1"></i> How It Works</a></li>
                    <li class="mb-2"><a href="{{ route('faq') }}" class="text-muted"><i class="fa fa-angle-right mr-1"></i> FAQ</a></li>
                    <li class="mb-2"><a href="{{ route('install') }}" class="text-muted"><i class="fa fa-wrench mr-1"></i> Install Guide</a></li>
                    <li class="mb-2"><a href="{{ route('api.docs') }}" class="text-info font-weight-bold"><i class="fa fa-code mr-1"></i> API & AI Docs</a></li>
                    <li class="mb-2"><a href="{{ route('about') }}" class="text-muted"><i class="fa fa-angle-right mr-1"></i> About Us</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}" class="text-muted"><i class="fa fa-angle-right mr-1"></i> Contact Us</a></li>
                </ul>
            </div>

            <!-- Download Tools -->
            <div class="col-lg-3 col-md-6 col-6 mb-4 text-center text-md-left">
                <h6 class="text-uppercase text-white font-weight-bold mb-3 border-bottom pb-2 border-secondary">Tools</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-youtube-play text-danger mr-1"></i> YouTube Downloader</a></li>
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-music text-warning mr-1"></i> MP3 Converter</a></li>
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-list-ul text-info mr-1"></i> Playlists</a></li>
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-facebook-official text-primary mr-1"></i> Facebook</a></li>
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted"><i class="fa fa-film text-success mr-1"></i> 1080p & 4K MP4</a></li>
                </ul>
            </div>

            <!-- Legal & Policies -->
            <div class="col-lg-3 col-md-6 col-12 mb-4 text-center text-md-left">
                <h6 class="text-uppercase text-white font-weight-bold mb-3 border-bottom pb-2 border-secondary">Legal & Privacy</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><a href="{{ route('privacy') }}" class="text-muted"><i class="fa fa-shield text-success mr-1"></i> Privacy Policy</a></li>
                    <li class="mb-2"><a href="{{ route('terms') }}" class="text-muted"><i class="fa fa-file-text-o mr-1"></i> Terms of Service</a></li>
                    <li class="mb-2"><a href="{{ route('dmca') }}" class="text-muted"><i class="fa fa-legal text-danger mr-1"></i> DMCA Policy</a></li>
                    <li class="mb-2"><a href="{{ route('admin.login') }}" class="text-muted"><i class="fa fa-lock text-warning mr-1"></i> Admin Portal</a></li>
                </ul>
                <div class="alert alert-secondary p-2 small mt-3 mb-0" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fa fa-server text-success"></i> <strong>Status:</strong> High-speed servers online.
                </div>

                @if(count($socials))
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start mt-3">
                        @foreach($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener"
                               class="text-muted mr-2" title="{{ $social['label'] }}">
                                <i class="fa {{ $social['icon'] }} fa-lg"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($supportEmail)
                    <p class="small text-muted mt-3 mb-0">
                        <i class="fa fa-envelope-o mr-1"></i> {{ $supportEmail }}
                    </p>
                @endif
            </div>
        </div>

        <hr class="border-secondary my-3">

        <!-- Legal Disclaimer & Copyright -->
        <div class="row align-items-center">
            <div class="col-md-8 col-12 small text-muted text-center text-md-left">
                <p class="mb-1">
                    <strong>Disclaimer:</strong> Zen X Download is an online software utility designed for personal fair-use archiving of public media. Zen X Download does not host or pirate copyright-protected files.
                </p>
                <p class="mb-0">
                    &copy; {{ date('Y') }} <strong>Zen X Download</strong>. All rights reserved.
                </p>
            </div>
            <div class="col-md-4 col-12 text-center text-md-right text-muted small mt-3 mt-md-0">
                <a href="{{ url('privacy-policy') }}" class="text-muted mr-3">Privacy</a>
                <a href="{{ url('terms-of-service') }}" class="text-muted mr-3">Terms</a>
                <a href="{{ url('contact') }}" class="text-muted mr-3">Support</a>
                <a href="{{ route('admin.login') }}" class="text-muted"><i class="fa fa-lock text-warning"></i> Admin</a>
            </div>
        </div>
    </div>
</footer>
