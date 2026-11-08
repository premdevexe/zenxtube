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
                                                $processUrl = url('download/process?url=' . urlencode($url) . '&type=' . $opt['type'] . '&height=' . $opt['height'] . '&title=' . urlencode($title));
                                                $directUrl = url('download/merge?url=' . urlencode($url) . '&type=' . $opt['type'] . '&height=' . $opt['height'] . '&title=' . urlencode($title));
                                            @endphp
                                            <tr>
                                                <td class="align-middle font-weight-bold">
                                                    {{ $opt['label'] }}
                                                </td>
                                                <td class="align-middle text-right">
                                                    @if($opt['type'] === 'audio')
                                                        <button type="button"
                                                                class="btn btn-warning btn-sm text-dark merge-download-btn shadow-sm font-weight-bold px-3"
                                                                data-process-url="{{ $processUrl }}"
                                                                data-direct-url="{{ $directUrl }}"
                                                                data-label="{{ $opt['label'] }}"
                                                                data-title="{{ $title }}"
                                                                data-ext="{{ $opt['ext'] }}">
                                                            <i class="fa fa-music mr-1"></i> Download {{ strtoupper($opt['ext']) }}
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-success btn-sm merge-download-btn shadow-sm font-weight-bold px-3"
                                                                data-process-url="{{ $processUrl }}"
                                                                data-direct-url="{{ $directUrl }}"
                                                                data-label="{{ $opt['label'] }}"
                                                                data-title="{{ $title }}"
                                                                data-ext="{{ $opt['ext'] }}">
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
                        <i id="modalIcon" class="fa fa-refresh fa-spin text-primary"></i>
                    </div>

                    <!-- Video Title & Format -->
                    <h5 id="modalVideoTitle" class="font-weight-bold mb-1 text-truncate" style="max-width: 100%;">Video Title</h5>
                    <p id="modalFormatBadge" class="badge badge-success px-3 py-1 mb-3">Format</p>

                    <!-- Status Text and Percentage -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span id="modalStatusText" class="step-indicator"><i class="fa fa-spinner fa-spin"></i> Processing media on server...</span>
                        <span id="modalPercentText" class="font-weight-bold text-success font-size-lg">15%</span>
                    </div>

                    <!-- Progress Bar Animation -->
                    <div class="progress mb-3" style="height: 14px; border-radius: 7px; background-color: #e9ecef;">
                        <div id="modalProgressBar" 
                             class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" 
                             style="width: 15%; transition: width 0.4s ease;">
                        </div>
                    </div>

                    <!-- Subtext Description -->
                    <p id="modalSubText" class="text-muted small mb-2">
                        Downloading high-speed video and audio streams and merging them with FFmpeg. Please wait...
                    </p>

                    <!-- Download Ready Action Area (Auto-triggers + direct click fallback) -->
                    <div id="modalDownloadActionArea" style="display: none;">
                        <a id="modalDownloadDirectBtn" href="#" class="btn btn-success btn-lg btn-block shadow-sm font-weight-bold py-3 mt-3 mb-2" style="border-radius: 12px; font-size: 1.1rem;">
                            <i class="fa fa-download mr-1"></i> <span id="modalDownloadBtnText">Save File</span>
                        </a>
                        <small class="text-muted d-block mb-3">
                            <i class="fa fa-info-circle mr-1"></i> If download did not start automatically, click the green button above.
                        </small>
                    </div>

                    <!-- Retry / Direct Stream Fallback Area -->
                    <div id="modalErrorActionArea" style="display: none;">
                        <a id="modalDirectFallbackBtn" href="#" class="btn btn-warning btn-block font-weight-bold py-2 mt-3 mb-2 text-dark" style="border-radius: 8px;">
                            <i class="fa fa-bolt mr-1"></i> Try Direct Download Link
                        </a>
                    </div>

                    <!-- Close Button -->
                    <button type="button" id="modalCloseBtn" class="btn btn-outline-secondary px-4 mt-2" data-dismiss="modal" style="display: none; border-radius: 20px;">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
let progressTimer = null;
let currentAbortCtrl = null;

function handleSearchSubmit(form) {
    const btn = document.getElementById('searchSubmitBtn');
    if (btn) {
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Analyzing Video...';
        btn.classList.add('disabled');
    }
}

window.addEventListener('pageshow', function() {
    const btn = document.getElementById('searchSubmitBtn');
    if (btn) {
        btn.innerHTML = '<i class="fa fa-cloud-download mr-1" aria-hidden="true"></i> Download';
        btn.classList.remove('disabled');
        btn.removeAttribute('disabled');
    }
});

// Delegated handler for merge download buttons
$(document).on('click', '.merge-download-btn', function () {
    const btn = this;
    const processUrl = btn.getAttribute('data-process-url');
    const directUrl  = btn.getAttribute('data-direct-url');
    const label      = btn.getAttribute('data-label') || '';
    const title      = btn.getAttribute('data-title') || '';
    const ext        = btn.getAttribute('data-ext') || 'mp4';

    openDownloadModal(processUrl, directUrl, label, title, ext);
});

function openDownloadModal(processUrl, directUrl, formatLabel, videoTitle, ext) {
    // Reset modal UI
    $('#modalVideoTitle').text(videoTitle);
    $('#modalFormatBadge').text(formatLabel);
    $('#modalPercentText').text('10%');
    $('#modalProgressBar').css('width', '10%').removeClass('bg-danger').addClass('bg-success');
    
    $('#modalIconContainer').addClass('download-icon-pulse').css('background', 'rgba(40, 167, 69, 0.12)');
    $('#modalIcon').attr('class', 'fa fa-refresh fa-spin text-primary');
    $('#modalStatusText').html('<i class="fa fa-spinner fa-spin"></i> Contacting video server...');
    $('#modalSubText').text('Fetching and merging high quality video & audio streams. Please wait...');
    
    $('#modalDownloadActionArea').hide();
    $('#modalErrorActionArea').hide();
    $('#modalCloseBtn').hide();

    // Show modal popup
    $('#downloadProgressModal').modal('show');

    // Smooth progressive progress animation while waiting for server response
    let percent = 10;
    if (progressTimer) clearInterval(progressTimer);

    progressTimer = setInterval(function() {
        if (percent < 30) {
            percent += 4;
            $('#modalStatusText').html('<i class="fa fa-bolt text-warning"></i> Connecting with 8 parallel streams...');
            $('#modalSubText').text('Downloading separate video & audio fragments at high speed...');
        } else if (percent < 60) {
            percent += 3;
            $('#modalStatusText').html('<i class="fa fa-download text-primary"></i> Downloading streams at maximum speed...');
        } else if (percent < 85) {
            percent += 2;
            $('#modalStatusText').html('<i class="fa fa-cogs fa-spin text-info"></i> Lossless stream merging with FFmpeg...');
            $('#modalSubText').text('Combining streams into a single high-quality ' + ext.toUpperCase() + ' file...');
        } else if (percent < 95) {
            percent += 1;
            $('#modalStatusText').html('<i class="fa fa-spinner fa-spin"></i> Finalizing media container...');
            $('#modalSubText').text('Preparing file attachment for your device...');
        }
        $('#modalProgressBar').css('width', percent + '%');
        $('#modalPercentText').text(percent + '%');
    }, 700);

    // Call server AJAX endpoint to process & merge
    fetch(processUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errData => {
                throw new Error(errData.message || ('Server error: ' + response.status));
            }).catch(e => {
                throw new Error('Server returned error (' + response.status + '). Please try another stream.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (progressTimer) clearInterval(progressTimer);

        if (data.success && data.download_url) {
            // Processing Succeeded!
            $('#modalProgressBar').css('width', '100%');
            $('#modalPercentText').text('100%');
            $('#modalIcon').attr('class', 'fa fa-check text-success');
            $('#modalIconContainer').removeClass('download-icon-pulse').css('background', 'rgba(40, 167, 69, 0.2)');
            $('#modalStatusText').html('<strong class="text-success"><i class="fa fa-check-circle"></i> File Ready!</strong>');
            $('#modalSubText').text('File processed (' + (data.filesize || '') + '). Starting download to your device...');

            // Configure Download Button
            $('#modalDownloadDirectBtn').attr('href', data.download_url);
            $('#modalDownloadBtnText').text('Save ' + ext.toUpperCase() + ' (' + (data.filesize || 'Download') + ')');
            $('#modalDownloadActionArea').show();
            $('#modalCloseBtn').show();

            // Trigger instantaneous native download
            window.location.href = data.download_url;
        } else {
            throw new Error(data.message || 'Processing failed.');
        }
    })
    .catch(err => {
        if (progressTimer) clearInterval(progressTimer);

        $('#modalProgressBar').css('width', '100%').removeClass('bg-success').addClass('bg-danger');
        $('#modalPercentText').text('Error');
        $('#modalIcon').attr('class', 'fa fa-exclamation-triangle text-danger');
        $('#modalIconContainer').removeClass('download-icon-pulse').css('background', 'rgba(220, 53, 69, 0.12)');
        $('#modalStatusText').html('<strong class="text-danger"><i class="fa fa-times-circle"></i> Processing Issue</strong>');
        $('#modalSubText').text(err.message || 'Failed to process media file. You can still use the direct stream below.');

        if (directUrl) {
            $('#modalDirectFallbackBtn').attr('href', directUrl);
            $('#modalErrorActionArea').show();
        }
        $('#modalCloseBtn').show();
    });
}

$('#downloadProgressModal').on('hidden.bs.modal', function () {
    if (progressTimer) clearInterval(progressTimer);
});
</script>
@endsection
