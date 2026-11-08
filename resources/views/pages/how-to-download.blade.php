@extends('app')

@section('title', 'How to Download Videos & Audio - Step by Step Tutorial - Zen X Download')

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <span class="badge badge-success px-3 py-1 font-weight-bold mb-2 text-white" style="border-radius: 12px; font-size: 0.78rem;">
            <i class="fa fa-book"></i> SIMPLE 3-STEP TUTORIAL
        </span>
        <h1 class="h3 font-weight-bold text-dark mb-1">How to Download Videos Online</h1>
        <p class="text-muted small mb-0">Follow our quick step-by-step guide to download MP4 videos in 4K, 1080p, or convert to MP3 audio</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            
            <!-- Quick 3-Step Walkthrough Cards -->
            <div class="row mb-5">
                <div class="col-md-4 col-12 mb-3">
                    <div class="card h-100 shadow-sm border-0 p-4 text-center bg-white">
                        <div class="mx-auto mb-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <span class="h3 font-weight-bold text-primary mb-0">1</span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-2">Copy Video Link</h5>
                        <p class="text-muted small mb-0">
                            Navigate to YouTube, Facebook, or Twitter. Copy the URL from your browser address bar or tap the <strong>"Share &rarr; Copy Link"</strong> button in mobile apps.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12 mb-3">
                    <div class="card h-100 shadow-sm border-0 p-4 text-center bg-white">
                        <div class="mx-auto mb-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <span class="h3 font-weight-bold text-success mb-0">2</span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-2">Paste &amp; Fetch</h5>
                        <p class="text-muted small mb-0">
                            Return to <a href="{{ url('/') }}">Zen X Download</a>, paste the link into the search box, and click the blue <strong>"Fetch Video"</strong> button.
                        </p>
                    </div>
                </div>

                <div class="col-md-4 col-12 mb-3">
                    <div class="card h-100 shadow-sm border-0 p-4 text-center bg-white">
                        <div class="mx-auto mb-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <span class="h3 font-weight-bold text-info mb-0">3</span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-2">Save MP4 or MP3</h5>
                        <p class="text-muted small mb-0">
                            Select your desired quality (4K, 1080p, 720p, or 320kbps MP3) and click <strong>"Download"</strong>. Your file saves immediately!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detailed Device Instructions -->
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white mb-4">
                <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-4">
                    <i class="fa fa-desktop text-primary mr-1"></i> Device-Specific Instructions
                </h4>

                <!-- PC / Mac -->
                <div class="mb-4">
                    <h5 class="font-weight-bold text-dark">
                        <i class="fa fa-windows text-info mr-1"></i> Windows PC &amp; Apple Mac
                    </h5>
                    <ol class="text-secondary small" style="line-height: 1.8;">
                        <li>Open your browser (Chrome, Edge, Firefox, or Safari).</li>
                        <li>Open the YouTube video you want to save. Highlight the full URL in the browser top address bar (e.g. <code>https://www.youtube.com/watch?v=...</code>) and press <code>Ctrl + C</code> (or <code>Cmd + C</code> on Mac).</li>
                        <li>Open <a href="{{ url('/') }}">zenxdownload.com</a>, press <code>Ctrl + V</code> in the input box, and click <strong>Fetch Video</strong>.</li>
                        <li>Click <strong>Download</strong> next to your preferred resolution. The file will stream straight into your computer's <em>Downloads</em> folder.</li>
                    </ol>
                </div>

                <hr class="my-4">

                <!-- Android -->
                <div class="mb-4">
                    <h5 class="font-weight-bold text-dark">
                        <i class="fa fa-android text-success mr-1"></i> Android Smartphones &amp; Tablets
                    </h5>
                    <ol class="text-secondary small" style="line-height: 1.8;">
                        <li>Open the YouTube app or Facebook app on your phone.</li>
                        <li>Under the video player, tap the <strong>Share</strong> button, then select <strong>Copy link</strong>.</li>
                        <li>Open Google Chrome or Samsung Internet, go to <a href="{{ url('/') }}">zenxdownload.com</a>, and tap and hold the input box to <strong>Paste</strong>.</li>
                        <li>Tap <strong>Fetch Video</strong>, select 1080p, 720p, or MP3, and tap <strong>Download</strong>. Your Android notification bar will show instant download progress.</li>
                    </ol>
                </div>

                <hr class="my-4">

                <!-- iPhone iOS -->
                <div class="mb-4">
                    <h5 class="font-weight-bold text-dark">
                        <i class="fa fa-apple text-dark mr-1"></i> iPhone &amp; iPad (iOS 13+)
                    </h5>
                    <ol class="text-secondary small" style="line-height: 1.8;">
                        <li>Open the YouTube app on your iPhone and tap <strong>Share &rarr; Copy link</strong>.</li>
                        <li>Open the built-in <strong>Safari</strong> browser and visit <a href="{{ url('/') }}">zenxdownload.com</a>.</li>
                        <li>Paste your link and tap <strong>Fetch Video</strong>.</li>
                        <li>Tap <strong>Download</strong>. Safari will display a pop-up: <em>"Do you want to download this file?"</em> Tap <strong>Download</strong>.</li>
                        <li>You can view and play your downloaded video in the <strong>Files &rarr; Downloads</strong> folder or save it to your Photos Camera Roll.</li>
                    </ol>
                </div>

                <hr class="my-4">

                <!-- YouTube Playlists -->
                <div>
                    <h5 class="font-weight-bold text-dark">
                        <i class="fa fa-list-ul text-warning mr-1"></i> Downloading YouTube Playlists
                    </h5>
                    <p class="text-secondary small mb-2">
                        Zen X Download natively supports playlist extraction. To download tracks from a playlist:
                    </p>
                    <ol class="text-secondary small" style="line-height: 1.8;">
                        <li>Copy the playlist URL (the URL must contain <code>list=PL...</code>).</li>
                        <li>Paste it into our search bar. Zen X Download will load the entire playlist catalog with video thumbnails, titles, and individual download buttons.</li>
                        <li>Click download on each track you want to keep.</li>
                    </ol>
                </div>

                <!-- Call to action -->
                <div class="mt-5 p-4 bg-light rounded text-center border">
                    <h5 class="font-weight-bold text-dark mb-2">Try it now with any video!</h5>
                    <p class="text-muted small mb-3">Copy your favourite link and test our high-speed downloader engine.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary px-4 font-weight-bold shadow-sm" style="border-radius: 20px;">
                        <i class="fa fa-arrow-circle-o-right mr-1"></i> Go to Downloader
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

