@php
    try {
        $activeAnnouncement = \App\Notification::where('is_banner_active', 1)
            ->whereIn('type', ['banner', 'both'])
            ->latest()
            ->first();
    } catch (\Exception $e) {
        $activeAnnouncement = null;
    }
@endphp

@if($activeAnnouncement)
    <div class="alert alert-light border-bottom mb-0 py-2 px-3 small text-secondary rounded-0 font-mono" style="font-size: 0.8rem; z-index: 1035; position: relative;" role="alert">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <span><strong>[NOTICE]</strong> {{ $activeAnnouncement->title }}: {{ $activeAnnouncement->message }}</span>
            @if($activeAnnouncement->link)
                <a href="{{ $activeAnnouncement->link }}" class="btn btn-sm btn-outline-dark py-0 px-2 my-1" style="font-size: 0.76rem; border-radius: 4px;">
                    Details &rarr;
                </a>
            @endif
        </div>
    </div>
@endif

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-2 main-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center py-0" href="{{ url('/') }}">
            <img src="{{ \App\SiteSetting::assetPath('site_logo', 'images/logo.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }}" height="36" class="d-inline-block align-middle" style="max-height: 36px; width: auto; object-fit: contain;">
        </a>

        <button class="navbar-toggler border-0 p-2" type="button" data-toggle="collapse" data-target="#zenNavbar" aria-controls="zenNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="zenNavbar">
            <ul class="navbar-nav ml-auto align-items-lg-center" style="font-size: 0.88rem;">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a class="nav-link px-2" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item {{ Request::is('how-to-download') ? 'active' : '' }}">
                    <a class="nav-link px-2" href="{{ route('how-to-download') }}">Workflow</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="{{ url('/#features') }}">Engine</a>
                </li>
                <li class="nav-item {{ Request::is('faq') ? 'active' : '' }}">
                    <a class="nav-link px-2" href="{{ route('faq') }}">FAQ</a>
                </li>
                <li class="nav-item {{ Request::is('about') ? 'active' : '' }}">
                    <a class="nav-link px-2" href="{{ route('about') }}">About</a>
                </li>
                <li class="nav-item {{ Request::is('contact') ? 'active' : '' }}">
                    <a class="nav-link px-2" href="{{ route('contact') }}">Contact</a>
                </li>
                <li class="nav-item ml-lg-3 mt-2 mt-lg-0">
                    <a href="{{ url('/') }}" class="btn btn-dark btn-sm px-3" style="border-radius: 6px; font-weight: 500; font-size: 0.84rem;">
                        <span>Download</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
