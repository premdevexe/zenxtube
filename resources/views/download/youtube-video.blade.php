@extends('app')

@section('styles')
<style>
.download-icon-pulse {
    width: 80px;
    height: 80px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: rgba(40, 167, 69, 0.12);
    color: #28a745;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    animation: pulseRing 1.8s infinite;
}

@keyframes pulseRing {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.45);
    }
    70% {
        box-shadow: 0 0 0 20px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.step-indicator {
    font-size: 13.5px;
    color: #495057;
    font-weight: 600;
}

.modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.22);
}

.modal-backdrop.show {
    opacity: 0.65;
}
</style>
@endsection

@section('content')

    <div class="jumbotron jumbotron-fluid bg-white border-bottom py-3 py-md-4 mb-4" style="box-shadow: inset 0 -10px 20px -10px rgba(0,0,0,0.03);">
        <div class="container text-center px-3 px-sm-4">
            <h1 class="h4 font-weight-bold text-dark mb-3">Download More Videos or Audio</h1>
            <form id="fd" action="{{ url('download') }}" method="post" onsubmit="handleSearchSubmit(this)" class="col-md-10 col-lg-8 mx-auto p-0">
                {{ csrf_field() }}
                <div class="input-group responsive-search-box shadow-sm">
                    <input name="videourl" value="{{ $url }}" required class="form-control responsive-search-input px-3 px-sm-4" placeholder="Enter Video Link..." type="text" autofocus>
                    <div class="input-group-append">
                        <button id="searchSubmitBtn" class="btn btn-primary responsive-search-btn">
                            <i class="fa fa-cloud-download mr-1" aria-hidden="true"></i> Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container mb-5 px-3 px-sm-4">
        <div class="row">
            <div class="col-lg-4 col-md-5 col-12 text-center mb-4">
                @if(!empty($thumbnail))
                    <img style="display: block; margin: 0 auto 15px; border: 1px solid #e2e8f0; border-radius: 10px; width: 100%; max-width: 320px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);" src="{{ $thumbnail }}" alt="{{ $title }}">
                @endif
                <h5 class="title mt-2 font-weight-bold text-dark" style="font-size: 1.05rem; word-break: break-word;">{{ $title }}</h5>
            </div>
            
            <div class="col-lg-8 col-md-7 col-12">
                @if(!empty($merged_options))
                    <div class="card mb-4 border-success shadow-sm">
                        <div class="card-header bg-success text-white font-weight-bold d-flex flex-wrap justify-content-between align-items-center py-2 px-3">
                            <span class="my-1"><i class="fa fa-download mr-1"></i> Ready to Download (MP4 & MP3)</span>
                            <span class="badge badge-light text-success font-weight-bold my-1">Sound Included</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Quality</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($merged_options as $opt)
                                            @php
                                                $mergeUrl = url('download/merge?url=' . urlencode($url) . '&type=' . $opt['type'] . '&height=' . $opt['height'] . '&title=' . urlencode($title));
                                            @endphp
                                            @php
                                                // JSON-encode with hex flags so uploader-controlled metadata
                                                // (label/title) cannot break out of the JS string (XSS-safe).
                                                $jsFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
                                            @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold">
                                                    {{ $opt['label'] }}
                                                </td>
                                                <td class="align-middle text-right">
                                                    @if($opt['type'] === 'audio')
                                                        <button type="button"
                                                                class="btn btn-warning btn-sm text-dark merge-download-btn shadow-sm font-weight-bold px-3"
                                                                data-url="{{ json_encode($mergeUrl, $jsFlags) }}"
                                                                data-label="{{ json_encode($opt['label'], $jsFlags) }}"
                                                                data-title="{{ json_encode($title, $jsFlags) }}">
                                                            <i class="fa fa-music mr-1"></i> Download {{ strtoupper($opt['ext']) }}
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-success btn-sm merge-download-btn shadow-sm font-weight-bold px-3"
                                                                data-url="{{ json_encode($mergeUrl, $jsFlags) }}"
                                                                data-label="{{ json_encode($opt['label'], $jsFlags) }}"
                                                                data-title="{{ json_encode($title, $jsFlags) }}">
                                                            <i class="fa fa-video-camera mr-1"></i> Download {{ strtoupper($opt['ext']) }}
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($links))
                    <div class="card border-secondary shadow-sm mb-4">
                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center py-2 px-3">
                            <span class="font-weight-bold my-1"><i class="fa fa-bolt text-warning mr-1"></i> Fast Direct Streams</span>
                            <small class="text-muted my-1">Direct CDN links</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Quality</th>
                                            <th>Size</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($links, 0, 8) as $link)
                                            @php
                                                $directDlUrl = url('download/file?url=' . urlencode($link['url']) . '&title=' . urlencode($title . '.' . $link['ext']));
                                            @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $link['format'] }}</td>
                                                <td class="align-middle"><span class="badge badge-light border">{{ $link['size'] ?? '---' }}</span></td>
                                                <td class="align-middle text-right">
                                                    @if(empty($link['has_video']) && !empty($link['has_audio']))
                                                        <a href="{{ $directDlUrl }}" class="btn btn-warning btn-sm text-dark font-weight-bold">
                                                            <i class="fa fa-music mr-1"></i> Download {{ strtoupper($link['ext']) }}
                                                        </a>
                                                    @else
                                                        <a href="{{ $directDlUrl }}" class="btn btn-primary btn-sm font-weight-bold">
                                                            <i class="fa fa-video-camera mr-1"></i> Download {{ strtoupper($link['ext']) }}
                                                        </a>
                                                    @endif
                                                    <a href="{{ $link['url'] }}" target="_blank" class="btn btn-outline-secondary btn-sm ml-1">
                                                        <i class="fa fa-play"></i> Stream
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Download Progress Modal Popup -->
    <div class="modal fade" id="downloadProgressModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered mx-auto px-2" style="max-width: 460px;" role="document">
            <div class="modal-content text-center p-3 p-sm-4 border-0 shadow-lg">
                <div class="modal-body p-1 p-sm-2">
                    <!-- Icon Animation -->
                    <div id="modalIconContainer" class="download-icon-pulse">
                        <i id="modalIcon" class="fa fa-cloud-download"></i>
                    </div>

                    <!-- Video Title & Format -->
                    <h5 id="modalVideoTitle" class="font-weight-bold mb-1 text-truncate" style="max-width: 100%;">Video Title</h5>
                    <p id="modalFormatBadge" class="badge badge-success px-3 py-1 mb-3">Format</p>

                    <!-- Status Text and Percentage -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span id="modalStatusText" class="step-indicator"><i class="fa fa-cog fa-spin"></i> Initializing download...</span>
                        <span id="modalPercentText" class="font-weight-bold text-success font-size-lg">0%</span>
                    </div>

                    <!-- Progress Bar Animation -->
                    <div class="progress mb-3" style="height: 14px; border-radius: 7px; background-color: #e9ecef;">
                        <div id="modalProgressBar" 
                             class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" 
                             style="width: 0%; transition: width 0.35s ease;">
                        </div>
                    </div>

                    <!-- Subtext Description -->
                    <p id="modalSubText" class="text-muted small mb-4">
                        Preparing your high-speed download file...
                    </p>

                    <!-- Close Button (Shown on complete) -->
                    <button type="button" id="modalCloseBtn" class="btn btn-outline-success px-4" data-dismiss="modal" style="display: none; border-radius: 20px;">
                        <i class="fa fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
let progressTimer = null;

function handleSearchSubmit(form) {
    const btn = document.getElementById('searchSubmitBtn');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Analyzing Video...';
    btn.classList.add('disabled');
}

// Merge buttons carry their data in JSON-encoded data-* attributes (XSS-safe).
// A delegated handler parses them and opens the modal — no inline JS string
// splicing of video metadata.
$(document).on('click', '.merge-download-btn', function () {
    const btn = this;
    const readJson = (key) => {
        const raw = btn.getAttribute('data-' + key);
        if (raw === null) return '';
        try {
            return JSON.parse(raw);
        } catch (e) {
            return raw;
        }
    };
    openDownloadModal(readJson('url'), readJson('label'), readJson('title'));
});

function openDownloadModal(downloadUrl, formatLabel, videoTitle) {
    // Reset modal UI
    $('#modalVideoTitle').text(videoTitle);
    $('#modalFormatBadge').text(formatLabel);
    $('#modalPercentText').text('0%');
    $('#modalProgressBar').css('width', '0%').removeClass('bg-info').addClass('bg-success');
    
    $('#modalIconContainer').addClass('download-icon-pulse').css('background', 'rgba(40, 167, 69, 0.12)');
    $('#modalIcon').attr('class', 'fa fa-cloud-download text-success');
    $('#modalStatusText').html('<i class="fa fa-cog fa-spin"></i> Connecting to video server...');
    $('#modalSubText').text('Fetching video and audio streams...');
    $('#modalCloseBtn').hide();

    // Show the modal popup
    $('#downloadProgressModal').modal('show');

    // Trigger download in hidden iframe so browser triggers Save Dialog without reloading page
    let oldIframe = document.getElementById('download_iframe');
    if (oldIframe) {
        oldIframe.remove();
    }
    const iframe = document.createElement('iframe');
    iframe.id = 'download_iframe';
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    iframe.src = downloadUrl;

    // Run multi-phase realistic progress animation
    let percent = 0;
    if (progressTimer) clearInterval(progressTimer);

    progressTimer = setInterval(function() {
        if (percent < 30) {
            percent += 6;
            $('#modalStatusText').html('<i class="fa fa-bolt text-warning"></i> Connecting with 8 parallel streams...');
        } else if (percent < 70) {
            percent += 5;
            $('#modalStatusText').html('<i class="fa fa-download text-primary"></i> High-speed multi-threaded download...');
            $('#modalSubText').text('Downloading separate high quality video & audio files...');
        } else if (percent < 90) {
            percent += 4;
            $('#modalStatusText').html('<i class="fa fa-cogs fa-spin text-info"></i> Fast stream muxing with FFmpeg...');
            $('#modalSubText').text('Combining streams into a single high-quality file...');
        } else if (percent < 98) {
            percent += 2;
            $('#modalStatusText').html('<i class="fa fa-spinner fa-spin"></i> Finalizing file transfer...');
            $('#modalSubText').text('Preparing file attachment for browser download...');
        } else if (percent >= 98 && percent < 100) {
            percent = 100;
            clearInterval(progressTimer);

            // Completed State
            $('#modalProgressBar').css('width', '100%');
            $('#modalPercentText').text('100%');
            $('#modalIcon').attr('class', 'fa fa-check text-success');
            $('#modalIconContainer').removeClass('download-icon-pulse').css('background', 'rgba(40, 167, 69, 0.2)');
            $('#modalStatusText').html('<strong class="text-success"><i class="fa fa-check-circle"></i> Download Started!</strong>');
            $('#modalSubText').text('Your file download has started. Check your browser downloads.');
            $('#modalCloseBtn').show();
        }

        $('#modalProgressBar').css('width', percent + '%');
        $('#modalPercentText').text(percent + '%');
    }, 220);
}

$('#downloadProgressModal').on('hidden.bs.modal', function () {
    if (progressTimer) clearInterval(progressTimer);
});
</script>
@endsection
