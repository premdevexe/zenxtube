@extends('installer.layout', ['step' => 4])

@section('title', 'Application Configuration')

@section('content')
    <div class="card-header-installer">
        <h2>Application and administrator</h2>
        <p>Set platform domain, system timezone, and define primary administrator credentials.</p>
    </div>

    <form method="POST" action="{{ route('installer.configuration.save') }}" id="configForm">
        @csrf

        <div class="card-body-installer">
            <!-- SECTION 1: SYSTEM SETTINGS -->
            <div class="mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-semibold small text-uppercase text-secondary font-mono">Platform parameters</span>
                    <span class="font-mono text-muted" style="font-size: 0.74rem;">[SYS:ENV]</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="app_name" class="form-label">Application name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="app_name" name="app_name" value="{{ old('app_name', $current['app_name'] ?? 'Zen X Download') }}" required placeholder="Zen X Download">
                    </div>

                    <div class="col-md-6">
                        <label for="app_url" class="form-label">Application base URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control font-mono" id="app_url" name="app_url" value="{{ old('app_url', $current['app_url'] ?? url('/')) }}" required placeholder="https://yourdomain.com">
                    </div>

                    <div class="col-md-6">
                        <label for="timezone" class="form-label">System timezone <span class="text-danger">*</span></label>
                        <select class="form-select font-mono" id="timezone" name="timezone" required>
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $current['timezone'] ?? 'UTC') === $tz ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="app_env" class="form-label">Environment mode <span class="text-danger">*</span></label>
                        <select class="form-select" id="app_env" name="app_env" required>
                            <option value="production" {{ old('app_env', $current['app_env'] ?? 'production') === 'production' ? 'selected' : '' }}>
                                Production (Disables debug output)
                            </option>
                            <option value="local" {{ old('app_env', $current['app_env'] ?? '') === 'local' ? 'selected' : '' }}>
                                Local / Development
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ADMINISTRATOR ACCOUNT -->
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-semibold small text-uppercase text-secondary font-mono">Administrator credentials</span>
                    <span class="font-mono text-muted" style="font-size: 0.74rem;">[AUTH:ADMIN]</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="admin_name" class="form-label">Administrator full name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ old('admin_name', $current['admin_name'] ?? 'System Administrator') }}" required placeholder="Administrator">
                    </div>

                    <div class="col-md-6">
                        <label for="admin_email" class="form-label">Administrator email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control font-mono" id="admin_email" name="admin_email" value="{{ old('admin_email', $current['admin_email'] ?? '') }}" required placeholder="admin@yourdomain.com">
                        <div class="form-text">Used for sign-in at <code>/admin/login</code>.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="admin_password" class="form-label">Administrator password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control font-mono" id="admin_password" name="admin_password" required minlength="8" placeholder="Minimum 8 characters">
                            <button type="button" class="btn btn-outline-secondary" id="toggleAdminPassBtn">
                                <i class="fa-regular fa-eye" id="toggleAdminPassIcon" style="font-size: 0.8rem;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="admin_password_confirmation" class="form-label">Confirm password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control font-mono" id="admin_password_confirmation" name="admin_password_confirmation" required minlength="8" placeholder="Re-enter password">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer-installer">
            <a href="{{ route('installer.database') }}" class="btn btn-zen-outline">
                <i class="fa-solid fa-arrow-left" style="font-size: 0.78rem;"></i>
                <span>Previous</span>
            </a>

            <button type="submit" class="btn btn-zen">
                <span>Begin installation</span>
                <i class="fa-solid fa-arrow-right" style="font-size: 0.78rem;"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    const togglePass = document.getElementById('toggleAdminPassBtn');
    const passInput = document.getElementById('admin_password');
    const passIcon = document.getElementById('toggleAdminPassIcon');

    togglePass.addEventListener('click', function() {
        if (passInput.type === 'password') {
            passInput.type = 'text';
            passIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passInput.type = 'password';
            passIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
</script>
@endsection
