@php
    $supportEmail = \App\SiteSetting::get('support_email', '');
    $socials = array_filter([
        \App\SiteSetting::get('social_facebook')  ? ['url' => \App\SiteSetting::get('social_facebook'), 'icon' => 'fa-facebook-official', 'label' => 'Facebook'] : null,
        \App\SiteSetting::get('social_twitter')   ? ['url' => \App\SiteSetting::get('social_twitter'), 'icon' => 'fa-twitter', 'label' => 'Twitter / X'] : null,
        \App\SiteSetting::get('social_instagram') ? ['url' => \App\SiteSetting::get('social_instagram'), 'icon' => 'fa-instagram', 'label' => 'Instagram'] : null,
        \App\SiteSetting::get('social_youtube')   ? ['url' => \App\SiteSetting::get('social_youtube'), 'icon' => 'fa-youtube-play', 'label' => 'YouTube'] : null,
    ]);
@endphp

<footer class="site-footer bg-dark text-light pt-5 pb-4 border-top" style="background-color: #0d1117 !important;">
    <div class="container">
        <div class="row">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6 col-12 mb-4 text-left">
                <div class="mb-2">
                    <img src="{{ \App\SiteSetting::assetPath('site_logo_white', 'images/logo-white.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }}" height="36" style="max-height: 36px; width: auto; object-fit: contain;">
                </div>
                <p class="text-muted small" style="line-height: 1.55;">
                    Direct video extraction and lossless audio remuxing utility for YouTube, Facebook, Twitter, and public streaming platforms.
                </p>
                <div class="mt-3 font-mono text-muted small" style="font-size: 0.74rem;">
                    <span>[PROTOCOL: HTTPS/TLS]</span> &bull; <span>[AUTO-PURGE: 15M]</span>
                </div>
            </div>

            <!-- Navigation -->
            <div class="col-lg-2 col-md-6 col-6 mb-4 text-left">
                <h6 class="text-white small fw-semibold mb-3 font-mono" style="font-size: 0.76rem;">PAGES</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-muted">Home</a></li>
                    <li class="mb-2"><a href="{{ route('how-to-download') }}" class="text-muted">Workflow</a></li>
                    <li class="mb-2"><a href="{{ route('faq') }}" class="text-muted">FAQ</a></li>
                    <li class="mb-2"><a href="{{ route('about') }}" class="text-muted">About</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}" class="text-muted">Contact</a></li>
                </ul>
            </div>

            <!-- Formats -->
            <div class="col-lg-3 col-md-6 col-6 mb-4 text-left">
                <h6 class="text-white small fw-semibold mb-3 font-mono" style="font-size: 0.76rem;">FORMATS</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><span class="text-muted">4K 2160p MP4 (avc1 / vp09)</span></li>
                    <li class="mb-2"><span class="text-muted">1080p Full HD 60fps</span></li>
                    <li class="mb-2"><span class="text-muted">720p HD MP4</span></li>
                    <li class="mb-2"><span class="text-muted">320kbps MP3 Audio</span></li>
                    <li class="mb-2"><span class="text-muted">M4A AAC Lossless</span></li>
                </ul>
            </div>

            <!-- Legal & Status -->
            <div class="col-lg-3 col-md-6 col-12 mb-4 text-left">
                <h6 class="text-white small fw-semibold mb-3 font-mono" style="font-size: 0.76rem;">POLICY &amp; ACCESS</h6>
                <ul class="list-unstyled footer-links small">
                    <li class="mb-2"><a href="{{ route('privacy') }}" class="text-muted">Privacy Policy</a></li>
                    <li class="mb-2"><a href="{{ route('terms') }}" class="text-muted">Terms of Service</a></li>
                    <li class="mb-2"><a href="{{ route('dmca') }}" class="text-muted">DMCA Notice</a></li>
                    <li class="mb-2"><a href="{{ route('admin.login') }}" class="text-muted">Admin Sign In</a></li>
                </ul>

                @if(count($socials))
                    <div class="d-flex flex-wrap mt-3" style="gap: 12px;">
                        @foreach($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="text-muted" title="{{ $social['label'] }}">
                                <i class="fa {{ $social['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($supportEmail)
                    <div class="small font-mono text-muted mt-2" style="font-size: 0.74rem;">
                        {{ $supportEmail }}
                    </div>
                @endif
            </div>
        </div>

        <hr class="border-secondary my-3" style="border-color: rgba(255,255,255,0.08) !important;">

        <!-- Disclaimer & Copyright -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted font-mono" style="font-size: 0.74rem;">
            <div>
                &copy; {{ date('Y') }} {{ \App\SiteSetting::get('site_name', 'Zen X Download') }}. Personal archival utility.
            </div>
            <div class="mt-2 mt-md-0">
                [SYSTEM: NOMINAL]
            </div>
        </div>
    </div>
</footer>
