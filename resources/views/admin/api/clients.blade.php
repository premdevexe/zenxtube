@extends('admin.layout')

@section('title', 'Registered API Clients')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-id-badge text-primary mr-2"></i> Registered API Clients & Applications
        </h2>
        <p class="text-muted small mb-0">Manage mobile applications (Android/iOS) and authorized frontend services granted access to the Zen X API Gateway.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <button type="button" class="btn btn-success btn-sm shadow-sm font-weight-bold" data-toggle="modal" data-target="#newClientModal">
            <i class="fa fa-plus-circle mr-1"></i> + Create New API Client
        </button>
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fa fa-exclamation-triangle mr-2"></i> {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('generated_secret'))
    <div class="card border-0 shadow mb-4 bg-dark text-white" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-2">
                <div class="stat-icon bg-warning text-dark mr-3 shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 24px;">
                    <i class="fa fa-key"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white font-weight-bold">API Credentials Generated Successfully!</h5>
                    <small class="text-white-50">Save your client secret now. For security purposes, this secret is hashed and will <strong>never be shown again</strong>.</small>
                </div>
            </div>
            
            <div class="p-3 rounded mt-3" style="background-color: #0f172a;">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-uppercase small font-weight-bold text-info">Client ID (X-Client-ID):</span>
                        <button type="button" class="btn btn-outline-info btn-xs py-0 px-2" onclick="copyToClipboard('{{ session('generated_client_id') }}', this)">
                            <i class="fa fa-copy"></i> Copy
                        </button>
                    </div>
                    <input type="text" readonly class="form-control form-control-sm bg-dark text-warning border-0 font-weight-bold font-monospace" value="{{ session('generated_client_id') }}" onclick="this.select()">
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-uppercase small font-weight-bold text-success">Client Secret (X-Client-Secret):</span>
                        <button type="button" class="btn btn-outline-success btn-xs py-0 px-2" onclick="copyToClipboard('{{ session('generated_secret') }}', this)">
                            <i class="fa fa-copy"></i> Copy
                        </button>
                    </div>
                    <input type="text" readonly class="form-control form-control-sm bg-dark text-success border-0 font-weight-bold font-monospace" value="{{ session('generated_secret') }}" onclick="this.select()">
                </div>

                <div class="small text-muted mt-2">
                    <i class="fa fa-info-circle text-info"></i> Include these headers in every API request from your mobile or client application:
                    <pre class="bg-black text-light p-2 rounded mt-1 mb-0 font-monospace small">X-Client-ID: {{ session('generated_client_id') }}&#10;X-Client-Secret: {{ session('generated_secret') }}</pre>
                </div>
            </div>
        </div>
    </div>
@endif

@if($clients->isEmpty())
    <div class="card shadow-sm border-0 text-center py-5 px-4 mb-4">
        <div class="my-3">
            <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fa fa-key fa-2x text-primary"></i>
            </span>
        </div>
        <h4 class="font-weight-bold text-dark mb-2">No API Clients Registered Yet</h4>
        <p class="text-muted mx-auto mb-4" style="max-width: 520px;">
            Test and dummy data has been purged. Create your first official API client application (Android APK, iOS, or Web Client) to generate production API credentials.
        </p>
        <div>
            <button type="button" class="btn btn-primary font-weight-bold px-4 py-2 shadow-sm" data-toggle="modal" data-target="#newClientModal">
                <i class="fa fa-plus-circle mr-1"></i> + Create New API Client
            </button>
        </div>
    </div>
@else
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Application Name</th>
                        <th>Platform</th>
                        <th>Client ID</th>
                        <th>Rate Limit</th>
                        <th>Connected Devices</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $client->name }}</div>
                            <small class="text-muted">Registered {{ $client->created_at ? $client->created_at->format('M d, Y') : 'N/A' }}</small>
                        </td>
                        <td>
                            @if($client->platform === 'android')
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-android mr-1"></i> Android</span>
                            @elseif($client->platform === 'ios')
                                <span class="badge badge-dark px-2 py-1"><i class="fa fa-apple mr-1"></i> iOS</span>
                            @elseif($client->platform === 'web')
                                <span class="badge badge-primary px-2 py-1"><i class="fa fa-globe mr-1"></i> Web SPA</span>
                            @else
                                <span class="badge badge-secondary px-2 py-1"><i class="fa fa-code mr-1"></i> Third-Party</span>
                            @endif
                        </td>
                        <td>
                            <code>{{ $client->client_id }}</code>
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $client->rate_limit_per_minute }} req/min</span>
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark">{{ $client->devices_count }}</span>
                        </td>
                        <td>
                            @if($client->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Disabled</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <form action="{{ route('admin.api.clients.toggle', $client->id) }}" method="POST" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-sm {{ $client->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $client->is_active ? 'Disable' : 'Enable' }}">
                                    <i class="fa {{ $client->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.api.clients.delete', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this API client? Registered devices will become orphaned.');">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Modal: New Client App -->
<div class="modal fade" id="newClientModal" tabindex="-1" role="dialog" aria-labelledby="newClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('admin.api.clients.create') }}" method="POST">
            {{ csrf_field() }}
            <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="newClientModalLabel">
                        <i class="fa fa-plus-circle mr-1"></i> Create API Key & Client Application
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Application Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Zen X Android App (Production)" required autofocus>
                        <small class="form-text text-muted">A descriptive name to identify this client application in logs.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Target Platform <span class="text-danger">*</span></label>
                        <select name="platform" class="form-control" required>
                            <option value="android">Android Application (APK)</option>
                            <option value="ios">iOS Application (IPA / App Store)</option>
                            <option value="web">Web SPA Client</option>
                            <option value="third_party">Third-Party External Service</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Custom Client ID <span class="text-muted small">(Optional)</span></label>
                        <input type="text" name="custom_client_id" class="form-control" placeholder="e.g. zenx-android-official (Leave blank to auto-generate)">
                        <small class="form-text text-muted">Leave empty to auto-generate a secure random client identifier.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Rate Limit (Req/min)</label>
                            <input type="number" name="rate_limit_per_minute" class="form-control" value="120" min="1" max="10000" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Allowed Origins</label>
                            <input type="text" name="allowed_origins" class="form-control" value="*" placeholder="*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4">
                        <i class="fa fa-key mr-1"></i> Generate API Credentials
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text, btn) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
    var original = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-check text-success"></i> Copied!';
    setTimeout(function() {
        btn.innerHTML = original;
    }, 2000);
}

$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
        $('#newClientModal').modal('show');
    }
});
</script>
@endsection
