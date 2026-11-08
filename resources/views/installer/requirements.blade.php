@extends('installer.layout', ['step' => 2])

@section('title', 'Server Requirements')

@section('content')
    <div class="card-header-installer">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2>Server prerequisites</h2>
                <p>Verifying PHP runtime, mandatory extensions, and storage permissions.</p>
            </div>
            <button id="recheckBtn" class="btn btn-sm btn-zen-outline">
                <i class="fa-solid fa-arrows-rotate me-1" style="font-size: 0.78rem;"></i> Re-check
            </button>
        </div>
    </div>

    <div class="card-body-installer">
        <!-- Status Notification Box -->
        <div id="statusBanner" class="alert {{ $requirements['passes'] ? 'alert-success' : 'alert-danger' }} d-flex align-items-center gap-3 mb-4 py-2 px-3 rounded-2" role="alert">
            <div class="small">
                @if($requirements['passes'])
                    <i class="fa-solid fa-check text-success me-2"></i><strong>Prerequisites satisfied.</strong> Environment and directory permissions meet requirements.
                @else
                    <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i><strong>Prerequisites missing.</strong> Please resolve items marked failed below before continuing.
                @endif
            </div>
        </div>

        <!-- 1. PHP Version -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center">
                <span>PHP RUNTIME</span>
                <span class="badge {{ $requirements['php']['passed'] ? 'badge-pass' : 'badge-fail' }}">
                    {{ $requirements['php']['passed'] ? 'PASS' : 'FAIL' }}
                </span>
            </div>
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small">
                    <div>
                        <span>Detected version:</span> <span class="font-mono fw-semibold text-dark">{{ $requirements['php']['current'] }}</span>
                        <span class="text-muted ms-2">(Required: &gt;= {{ $requirements['php']['required'] }})</span>
                    </div>
                    <div class="text-muted font-mono" style="font-size: 0.78rem;">
                        {{ $requirements['php']['message'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Extensions Table -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center">
                <span>PHP EXTENSIONS</span>
                <span class="font-mono text-muted" style="font-size: 0.78rem;">
                    {{ count($requirements['extensions']['items']) - $requirements['extensions']['failed_count'] }}/{{ count($requirements['extensions']['items']) }} loaded
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light font-mono" style="font-size: 0.76rem;">
                        <tr>
                            <th class="ps-3" style="width: 25%;">Extension</th>
                            <th style="width: 60%;">Functional requirement</th>
                            <th class="text-end pe-3" style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.84rem;">
                        @foreach($requirements['extensions']['items'] as $extName => $extData)
                            <tr>
                                <td class="ps-3 font-mono">{{ $extName }}</td>
                                <td class="text-muted">{{ $extData['purpose'] }}</td>
                                <td class="text-end pe-3">
                                    <span class="badge {{ $extData['loaded'] ? 'badge-pass' : 'badge-fail' }}">
                                        {{ $extData['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Directory Permissions Table -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center">
                <span>DIRECTORY WRITABILITY</span>
                <span class="font-mono text-muted" style="font-size: 0.78rem;">
                    {{ count($requirements['permissions']['items']) - $requirements['permissions']['failed_count'] }}/{{ count($requirements['permissions']['items']) }} writable
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light font-mono" style="font-size: 0.76rem;">
                        <tr>
                            <th class="ps-3" style="width: 45%;">Path</th>
                            <th style="width: 20%;">Required</th>
                            <th style="width: 20%;">Detected</th>
                            <th class="text-end pe-3" style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.84rem;">
                        @foreach($requirements['permissions']['items'] as $folder => $permData)
                            <tr>
                                <td class="ps-3 font-mono text-dark">{{ $folder }}</td>
                                <td class="text-muted font-mono">{{ $permData['required'] }}</td>
                                <td class="text-muted font-mono">{{ $permData['current'] }}</td>
                                <td class="text-end pe-3">
                                    <span class="badge {{ $permData['is_writable'] ? 'badge-pass' : 'badge-fail' }}">
                                        {{ $permData['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Environment File -->
        <div class="card border rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center">
                <span>CONFIGURATION FILE (.ENV)</span>
                <span class="badge {{ $requirements['environment']['passed'] ? 'badge-pass' : 'badge-fail' }}">
                    {{ $requirements['environment']['status'] }}
                </span>
            </div>
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 small">
                    <span class="font-mono text-dark">.env</span>
                    <span class="text-muted">{{ $requirements['environment']['message'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer-installer">
        <a href="{{ route('installer.welcome') }}" class="btn btn-zen-outline">
            <i class="fa-solid fa-arrow-left" style="font-size: 0.78rem;"></i>
            <span>Previous</span>
        </a>

        @if($requirements['passes'])
            <a href="{{ route('installer.database') }}" class="btn btn-zen">
                <span>Continue to database</span>
                <i class="fa-solid fa-arrow-right" style="font-size: 0.78rem;"></i>
            </a>
        @else
            <button class="btn btn-zen" disabled>
                <span>Requirements blocked</span>
                <i class="fa-solid fa-lock" style="font-size: 0.78rem;"></i>
            </button>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('recheckBtn').addEventListener('click', function() {
        const btn = this;
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin me-1"></i> Checking...';

        fetch("{{ route('installer.requirements.check') }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => {
            window.location.reload();
        })
        .catch(err => {
            alert('Re-check failed: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
    });
</script>
@endsection
