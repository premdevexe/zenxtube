@extends('admin.layout')

@section('title', 'API Platform Settings')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-sliders text-warning mr-2"></i> API Platform Configuration
        </h2>
        <p class="text-muted small mb-0">Configure dynamic gateway endpoints, maintenance switches, client version enforcement, and rate limits.</p>
    </div>
    <div class="col-md-4 text-md-right mt-3 mt-md-0">
        <a href="{{ route('admin.api.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left mr-1"></i> Back to Overview
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<form action="{{ route('admin.api.settings.update') }}" method="POST">
    {{ csrf_field() }}

    <!-- 1. Gateway & Maintenance Controls -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fa fa-server text-primary mr-2"></i> Gateway Routing & Maintenance Gate
            </h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="font-weight-bold mb-0">Primary API Gateway Base URL</label>
                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 font-weight-bold" onclick="autoDetectApiBaseUrl()" title="Auto-detect current domain & protocol and populate input">
                            <i class="fa fa-magic mr-1"></i> Auto Fetch Live Domain
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="text" id="api_base_url_input" name="api_base_url" class="form-control font-mono" value="{{ $baseUrl ?? ($settings['api_base_url']->value ?? url('/api/v1')) }}" required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="syncWithLiveServer(this)" title="Instantly sync & save with current host domain">
                                <i class="fa fa-refresh mr-1"></i> Sync &amp; Save
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">The public HTTPS gateway address distributed to mobile apps &amp; SDKs. Click <strong>Auto Fetch Live Domain</strong> to adapt to your active domain or host.</small>
                    <div id="sync_feedback" class="small mt-1" style="display:none;"></div>
                </div>
                <div class="form-group col-md-4">
                    <label class="font-weight-bold">Gateway Maintenance Mode</label>
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="api_maintenance_mode" name="api_maintenance_mode" value="1" {{ !empty($settings['api_maintenance_mode']->value) && $settings['api_maintenance_mode']->value == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold text-danger" for="api_maintenance_mode">Enable API Maintenance</label>
                    </div>
                    <small class="form-text text-muted">Returns HTTP 503 to all client API requests.</small>
                </div>
            </div>

            <div class="form-group mb-0">
                <label class="font-weight-bold">Maintenance Broadcast Message</label>
                <textarea name="api_maintenance_message" class="form-control" rows="2">{{ $settings['api_maintenance_message']->value ?? 'Zen X API platform is currently undergoing scheduled maintenance. Please check back shortly.' }}</textarea>
            </div>
        </div>
    </div>

    <!-- 2. Mobile App Version Management -->
    <div class="row">
        <!-- Android Version Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fa fa-android text-success fa-lg mr-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">Android App Version Control</h6>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="font-weight-bold small">Minimum Supported Version</label>
                            <input type="text" name="android_min_version" class="form-control form-control-sm" value="{{ $settings['android_min_version']->value ?? '1.0.0' }}" required>
                            <small class="text-muted">Below this requires update</small>
                        </div>
                        <div class="form-group col-6">
                            <label class="font-weight-bold small">Latest Release Version</label>
                            <input type="text" name="android_latest_version" class="form-control form-control-sm" value="{{ $settings['android_latest_version']->value ?? '1.0.0' }}" required>
                            <small class="text-muted">Latest in production</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">APK Download / Update URL</label>
                        <input type="text" name="android_update_url" class="form-control form-control-sm" value="{{ $settings['android_update_url']->value ?? url('/download/app.apk') }}" required>
                    </div>

                    <div class="custom-control custom-checkbox mt-3">
                        <input type="checkbox" class="custom-control-input" id="android_force_update" name="android_force_update" value="1" {{ !empty($settings['android_force_update']->value) && $settings['android_force_update']->value == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold text-danger small" for="android_force_update">Force Update for All Android Users</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- iOS Version Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <i class="fa fa-apple text-dark fa-lg mr-2"></i>
                    <h6 class="m-0 font-weight-bold text-dark">iOS App Version Control</h6>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="font-weight-bold small">Minimum Supported Version</label>
                            <input type="text" name="ios_min_version" class="form-control form-control-sm" value="{{ $settings['ios_min_version']->value ?? '1.0.0' }}" required>
                            <small class="text-muted">Below this requires update</small>
                        </div>
                        <div class="form-group col-6">
                            <label class="font-weight-bold small">Latest Release Version</label>
                            <input type="text" name="ios_latest_version" class="form-control form-control-sm" value="{{ $settings['ios_latest_version']->value ?? '1.0.0' }}" required>
                            <small class="text-muted">Latest in App Store</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold small">App Store URL</label>
                        <input type="text" name="ios_update_url" class="form-control form-control-sm" value="{{ $settings['ios_update_url']->value ?? 'https://apps.apple.com/app/zen-x-download/id00000000' }}" required>
                    </div>

                    <div class="custom-control custom-checkbox mt-3">
                        <input type="checkbox" class="custom-control-input" id="ios_force_update" name="ios_force_update" value="1" {{ !empty($settings['ios_force_update']->value) && $settings['ios_force_update']->value == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold text-danger small" for="ios_force_update">Force Update for All iOS Users</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Limits & Security Throttling -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-dark">
                <i class="fa fa-shield text-info mr-2"></i> Storage Limits & Sliding-Window Rate Limits
            </h6>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="font-weight-bold">Max File Download Size (MB)</label>
                    <input type="number" name="max_download_file_size_mb" class="form-control" value="{{ $settings['max_download_file_size_mb']->value ?? '1000' }}" min="50" max="10000" required>
                    <small class="text-muted">Maximum allowed media filesize processed per request.</small>
                </div>
                <div class="form-group col-md-6">
                    <label class="font-weight-bold">Storage Retention Window (Minutes)</label>
                    <input type="number" name="download_file_retention_minutes" class="form-control" value="{{ $settings['download_file_retention_minutes']->value ?? '15' }}" min="5" max="1440" required>
                    <small class="text-muted">Files are purged automatically after this duration.</small>
                </div>
            </div>

            <hr>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="font-weight-bold small">Public Endpoints Limit (/min)</label>
                    <input type="number" name="rate_limit_public_per_min" class="form-control" value="{{ $settings['rate_limit_public_per_min']->value ?? '60' }}" required>
                    <small class="text-muted">Per IP address per minute</small>
                </div>
                <div class="form-group col-md-4">
                    <label class="font-weight-bold small">Authenticated Endpoints Limit (/min)</label>
                    <input type="number" name="rate_limit_auth_per_min" class="form-control" value="{{ $settings['rate_limit_auth_per_min']->value ?? '120' }}" required>
                    <small class="text-muted">Per active token per minute</small>
                </div>
                <div class="form-group col-md-4">
                    <label class="font-weight-bold small">Download Extractions Limit (/min)</label>
                    <input type="number" name="rate_limit_download_per_min" class="form-control" value="{{ $settings['rate_limit_download_per_min']->value ?? '15' }}" required>
                    <small class="text-muted">Per device per minute</small>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right mb-5">
        <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow-sm">
            <i class="fa fa-save mr-1"></i> Save Platform Settings
        </button>
    </div>
</form>

<script>
function autoDetectApiBaseUrl() {
    var liveOrigin = window.location.origin;
    var detectedUrl = liveOrigin.replace(/\/+$/, '') + '/api/v1';
    var input = document.getElementById('api_base_url_input');
    input.value = detectedUrl;
    
    var feedback = document.getElementById('sync_feedback');
    feedback.style.display = 'block';
    feedback.className = 'small mt-1 text-success font-weight-bold';
    feedback.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Detected current domain: ' + detectedUrl + ' (Click "Save Platform Settings" to persist)';
    
    input.style.backgroundColor = '#e8f5e9';
    setTimeout(function() {
        input.style.backgroundColor = '';
    }, 1200);
}

function syncWithLiveServer(btn) {
    var originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Syncing...';

    fetch('{{ route("admin.api.settings.auto_sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (data.success && data.base_url) {
            document.getElementById('api_base_url_input').value = data.base_url;
            var feedback = document.getElementById('sync_feedback');
            feedback.style.display = 'block';
            feedback.className = 'small mt-1 text-success font-weight-bold';
            feedback.innerHTML = '<i class="fa fa-check-circle mr-1"></i> ' + data.message;
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        autoDetectApiBaseUrl();
    });
}
</script>
@endsection
