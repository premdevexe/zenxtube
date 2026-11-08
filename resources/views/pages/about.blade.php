@extends('app')

@php
    $siteName = \App\SiteSetting::get('site_name', config('app.name', 'Zen X Download'));
@endphp

@section('title', 'About Us - ' . $siteName)

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <span class="badge badge-primary px-3 py-1 font-weight-bold mb-2" style="border-radius: 12px; font-size: 0.78rem;">
            <i class="fa fa-bolt"></i> FAST & SECURE MEDIA ENGINE
        </span>
        <h1 class="h3 font-weight-bold text-dark mb-1">About {{ $siteName }}</h1>
        <p class="text-muted small mb-0">High-speed, privacy-first online video & audio downloader</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white mb-4">
                <section class="mb-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="font-weight-bold text-dark mb-3">Our Mission</h3>
                            <p class="text-secondary lead" style="font-size: 1.05rem;">
                                <strong>{{ $siteName }}</strong> was built with a single goal: to provide an ultra-fast, unrestricted, and completely safe way to save and convert online media without intrusive adware, forced software installations, or bandwidth throttling.
                            </p>
                            <p class="text-secondary">
                                Unlike traditional downloader utilities that bombard visitors with deceptive redirect ads, pop-ups, and slow download caps, {{ $siteName }} prioritizes clean user experience, instant multi-threaded streaming, and strict user privacy.
                            </p>
                        </div>
                        <div class="col-md-4 text-center my-3 my-md-0">
                            <img src="{{ \App\SiteSetting::assetPath('site_logo', 'images/logo-icon.svg') }}" alt="{{ $siteName }} Emblem" class="img-fluid" style="max-width: 140px; filter: drop-shadow(0 10px 15px rgba(0, 98, 224, 0.2));">
                        </div>
                    </div>
                </section>

                <section class="mb-5">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-4">
                        <i class="fa fa-cogs text-primary mr-1"></i> Core Technology & Advantages
                    </h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fa fa-bolt text-warning mr-1"></i> 8-Stream Multi-Threaded Engine
                                </h6>
                                <p class="text-muted small mb-0">
                                    Our backend architecture uses 8 concurrent parallel fragment streams with high-throughput chunks, providing download speeds up to 5x faster than typical single-stream converters.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fa fa-film text-info mr-1"></i> True 4K, 1080p & 60FPS Merging
                                </h6>
                                <p class="text-muted small mb-0">
                                    Many downloaders fail on 1080p and 4K YouTube videos because YouTube stores video and audio tracks separately. {{ $siteName }} automatically combines pristine video and audio on-the-fly.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fa fa-headphones text-success mr-1"></i> Studio-Quality MP3 & M4A Audio
                                </h6>
                                <p class="text-muted small mb-0">
                                    Extract pure audio streams from music videos, interviews, podcasts, and speeches up to 320kbps without lossy re-encoding delays.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="font-weight-bold text-dark mb-2">
                                    <i class="fa fa-shield text-danger mr-1"></i> Privacy & Zero Tracking
                                </h6>
                                <p class="text-muted small mb-0">
                                    No accounts required, no browser history tracking, and no persistent retention of downloaded media files. Your downloads are direct and private.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                        <i class="fa fa-check-circle text-success mr-1"></i> How {{ $siteName }} Compares
                    </h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center mb-0" style="font-size: 0.9rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-left">Feature</th>
                                    <th class="text-primary font-weight-bold">{{ $siteName }}</th>
                                    <th class="text-muted">Other Converters</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left font-weight-bold">4K, 1080p & 720p HD</td>
                                    <td class="text-success"><i class="fa fa-check"></i> Supported (Full Audio)</td>
                                    <td class="text-danger"><i class="fa fa-times"></i> Often Muted or Capped to 360p</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold">Download Acceleration</td>
                                    <td class="text-success"><i class="fa fa-check"></i> 8 Parallel Streams</td>
                                    <td class="text-muted">Single Stream (Throttled)</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold">Pop-Up & Redirect Ads</td>
                                    <td class="text-success"><i class="fa fa-check"></i> Clean & Safe</td>
                                    <td class="text-danger"><i class="fa fa-times"></i> Aggressive Pop-ups</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold">Software Installation</td>
                                    <td class="text-success"><i class="fa fa-check"></i> 100% Web Based</td>
                                    <td class="text-warning"><i class="fa fa-exclamation-triangle"></i> Requires Executable / Plugins</td>
                                </tr>
                                <tr>
                                    <td class="text-left font-weight-bold">Playlist Support</td>
                                    <td class="text-success"><i class="fa fa-check"></i> Full YouTube Playlists</td>
                                    <td class="text-danger"><i class="fa fa-times"></i> Single Videos Only</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="mt-4 p-4 bg-light rounded text-center border">
                    <h5 class="font-weight-bold text-dark mb-2">Ready to test our download speed?</h5>
                    <p class="text-muted small mb-3">Paste any YouTube, Facebook, or Twitter video link to start downloading immediately.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary px-4 font-weight-bold shadow-sm" style="border-radius: 20px;">
                        <i class="fa fa-bolt mr-1"></i> Start Downloading Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

