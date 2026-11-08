@extends('app')

@section('title', 'Frequently Asked Questions (FAQ) - Zen X Download')

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <span class="badge badge-info px-3 py-1 font-weight-bold mb-2 text-white" style="border-radius: 12px; font-size: 0.78rem;">
            <i class="fa fa-question-circle"></i> HELP & KNOWLEDGE BASE
        </span>
        <h1 class="h3 font-weight-bold text-dark mb-1">Frequently Asked Questions</h1>
        <p class="text-muted small mb-0">Everything you need to know about downloading videos and audio with Zen X Download</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white mb-4">
                
                <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-4">
                    <i class="fa fa-bolt text-primary mr-1"></i> General & How to Use
                </h4>

                <div class="accordion mb-5" id="faqAccordionGeneral">
                    <!-- FAQ 1 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading1">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none" type="button" data-toggle="collapse" data-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> How do I download a video or audio file?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse1" class="collapse show" aria-labelledby="faqHeading1" data-parent="#faqAccordionGeneral">
                            <div class="card-body text-secondary pt-0">
                                Downloading is simple and takes 3 steps:
                                <ol class="mt-2 mb-0">
                                    <li>Copy the video URL from YouTube, Facebook, Twitter, or supported site.</li>
                                    <li>Paste the URL into the search box on the <a href="{{ url('/') }}">Zen X Download Homepage</a> and click <strong>"Fetch Video"</strong>.</li>
                                    <li>Choose your desired resolution (4K, 1080p, 720p, etc.) or audio format (MP3, M4A) and click <strong>"Download"</strong>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading2">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> Is Zen X Download completely free?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse2" class="collapse" aria-labelledby="faqHeading2" data-parent="#faqAccordionGeneral">
                            <div class="card-body text-secondary pt-0">
                                Yes! Zen X Download is 100% free with unlimited downloads. There are no registration forms, credit cards, or hidden fees required.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading3">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> Do I need to install software or browser extensions?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse3" class="collapse" aria-labelledby="faqHeading3" data-parent="#faqAccordionGeneral">
                            <div class="card-body text-secondary pt-0">
                                No. Zen X Download is completely web-based and works in any modern web browser (Google Chrome, Mozilla Firefox, Safari, Microsoft Edge, Opera, Samsung Internet) without installing extensions or third-party executable apps.
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-4">
                    <i class="fa fa-film text-info mr-1"></i> Formats, Resolutions & Playlists
                </h4>

                <div class="accordion mb-5" id="faqAccordionQuality">
                    <!-- FAQ 4 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading4">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> What resolutions are supported? Can I download in 1080p and 4K?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse4" class="collapse" aria-labelledby="faqHeading4" data-parent="#faqAccordionQuality">
                            <div class="card-body text-secondary pt-0">
                                Yes! Whenever available on the original video, Zen X Download supports <strong>4K (2160p), 1440p (2K), 1080p (Full HD), 720p (HD), 480p, and 360p</strong>. Our engine automatically merges separate high-resolution video and audio streams so you get a complete video file with crystal-clear sound.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading5">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> How do I convert YouTube videos to MP3?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse5" class="collapse" aria-labelledby="faqHeading5" data-parent="#faqAccordionQuality">
                            <div class="card-body text-secondary pt-0">
                                After pasting the video link and fetching its details, scroll to the <strong>"Audio Formats"</strong> table and select the MP3 option. Our engine will rip and stream the audio directly to your device at maximum quality (up to 320kbps).
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading6">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> Can I download entire YouTube playlists?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse6" class="collapse" aria-labelledby="faqHeading6" data-parent="#faqAccordionQuality">
                            <div class="card-body text-secondary pt-0">
                                Yes. When you paste a YouTube playlist link (`list=...`), Zen X Download detects the playlist and loads all videos in a clean list, allowing you to select and download individual tracks or videos with one click.
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="font-weight-bold text-dark border-bottom pb-2 mb-4">
                    <i class="fa fa-mobile text-success mr-1"></i> Mobile & Device Questions
                </h4>

                <div class="accordion mb-4" id="faqAccordionDevices">
                    <!-- FAQ 7 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading7">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse7" aria-expanded="false" aria-controls="faqCollapse7">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> Does Zen X Download work on Android and iPhone (iOS)?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse7" class="collapse" aria-labelledby="faqHeading7" data-parent="#faqAccordionDevices">
                            <div class="card-body text-secondary pt-0">
                                Yes, Zen X Download is fully responsive:
                                <ul class="mt-2 mb-0">
                                    <li><strong>Android:</strong> Open Chrome, Brave, or Samsung Internet, paste the link, and tap download. Files save directly to your device's <em>Downloads</em> folder.</li>
                                    <li><strong>iPhone / iPad (iOS 13+):</strong> Open Safari, paste the link, and tap download. Safari will prompt you to save the file into the <em>Files</em> app.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 8 -->
                    <div class="card border mb-2 shadow-none rounded">
                        <div class="card-header bg-white py-3" id="faqHeading8">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-dark font-weight-bold text-left d-flex justify-content-between align-items-center w-100 p-0 text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#faqCollapse8" aria-expanded="false" aria-controls="faqCollapse8">
                                    <span><i class="fa fa-question-circle text-primary mr-2"></i> Where are downloaded files saved on my device?</span>
                                    <i class="fa fa-chevron-down text-muted small"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="faqCollapse8" class="collapse" aria-labelledby="faqHeading8" data-parent="#faqAccordionDevices">
                            <div class="card-body text-secondary pt-0">
                                Files are automatically saved to your operating system's default download location:
                                <ul class="mt-2 mb-0">
                                    <li><strong>Windows / Mac:</strong> Your browser's default <code>Downloads</code> folder (press <code>Ctrl + J</code> in Chrome or <code>Cmd + Option + L</code> on Mac to view).</li>
                                    <li><strong>Android:</strong> The <em>Files</em> or <em>My Files &rarr; Downloads</em> directory.</li>
                                    <li><strong>iOS:</strong> The <em>Files &rarr; Downloads</em> folder.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-light rounded text-center border mt-4">
                    <h5 class="font-weight-bold text-dark mb-2">Have a question not listed here?</h5>
                    <p class="text-muted small mb-3">Our support team is here to help with any technical issues or inquiries.</p>
                    <a href="{{ route('contact') }}" class="btn btn-outline-primary px-4 font-weight-bold" style="border-radius: 20px;">
                        <i class="fa fa-envelope-o mr-1"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

