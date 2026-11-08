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
    <div class="alert alert-{{ $activeAnnouncement->banner_style ?? 'info' }} alert-dismissible fade show mb-0 text-center py-2 px-3 border-0 rounded-0 font-weight-bold" style="font-size: 0.9rem; z-index: 1035; position: relative;" role="alert">
        <div class="container d-flex flex-wrap justify-content-center align-items-center">
            <span class="mr-2"><i class="fa fa-bullhorn mr-1"></i> <strong>{{ $activeAnnouncement->title }}:</strong> {{ $activeAnnouncement->message }}</span>
            @if($activeAnnouncement->link)
                <a href="{{ $activeAnnouncement->link }}" class="btn btn-sm btn-dark py-0 px-2 my-1 font-weight-bold" style="font-size: 0.8rem; border-radius: 12px;">
                    Learn More &rarr;
                </a>
            @endif
        </div>
        <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close" style="font-size: 1.2rem;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-2 main-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center py-0" href="{{ url('/') }}">
            <!-- Zen X Download Brand Logo -->
            <img src="{{ \App\SiteSetting::assetPath('site_logo', 'images/logo.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }} - Fast & Free Video Downloader" height="42" class="d-inline-block align-middle" style="max-height: 42px; width: auto; object-fit: contain;">
        </a>

        <button class="navbar-toggler border-0 p-2" type="button" data-toggle="collapse" data-target="#zenNavbar" aria-controls="zenNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="zenNavbar">
            <ul class="navbar-nav ml-auto align-items-lg-center">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ url('/') }}">
                        <i class="fa fa-home text-primary"></i> Home
                    </a>
                </li>
                <li class="nav-item {{ Request::is('how-to-download') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ route('how-to-download') }}">
                        <i class="fa fa-book text-success"></i> How It Works
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" href="{{ url('/#features') }}">
                        <i class="fa fa-bolt text-warning"></i> Features
                    </a>
                </li>
                <li class="nav-item {{ Request::is('faq') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ route('faq') }}">
                        <i class="fa fa-question-circle text-secondary"></i> FAQ
                    </a>
                </li>
                <li class="nav-item {{ Request::is('about') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ route('about') }}">
                        <i class="fa fa-info-circle text-info"></i> About
                    </a>
                </li>
                <li class="nav-item {{ Request::is('contact') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ route('contact') }}">
                        <i class="fa fa-envelope-o"></i> Contact
                    </a>
                </li>
                <li class="nav-item {{ Request::is('install*') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold" href="{{ route('install') }}" title="Installation & Deployment Guide">
                        <i class="fa fa-wrench text-secondary"></i> Install
                    </a>
                </li>
                <li class="nav-item {{ Request::is('api/docs') || Request::is('docs') ? 'active' : '' }}">
                    <a class="nav-link font-weight-bold text-primary" href="{{ route('api.docs') }}" title="REST API v1 Specification & AI Prompts">
                        <i class="fa fa-code"></i> API & AI
                    </a>
                </li>
                <li class="nav-item ml-lg-2 mt-2 mt-lg-0">
                    <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm font-weight-bold d-block d-lg-inline-block text-center" style="border-radius: 20px;" onclick="enablePushNotifications()" id="enableNotifBtn" title="Enable Browser Notifications">
                        <i class="fa fa-bell" id="enableNotifIcon"></i> <span id="enableNotifText">Alerts</span>
                    </button>
                </li>
                <li class="nav-item ml-lg-2 mt-2 mt-lg-0">
                    <a href="{{ url('/') }}" class="btn btn-primary btn-sm px-3 shadow-sm font-weight-bold download-nav-btn d-block d-lg-inline-block text-center">
                        <i class="fa fa-download"></i> Paste & Download
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
