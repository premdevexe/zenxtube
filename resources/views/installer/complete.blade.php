@extends('installer.layout', ['step' => 6])

@section('title', 'Installation Complete')

@section('content')
    <div class="card-header-installer py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2>Installation verified</h2>
                <p>Application schemas, production environment, and security locks initialized.</p>
            </div>
            <span class="sys-tag font-mono">[LOCK: CREATED]</span>
        </div>
    </div>

    <div class="card-body-installer">
        <!-- Configuration Summary Card -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center font-mono" style="font-size: 0.76rem;">
                <span>DEPLOYMENT METRICS</span>
                <span class="text-success">[ACTIVE]</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.84rem;">
                    <tbody>
                        <tr>
                            <td class="text-secondary ps-3" style="width: 35%;">Application URL</td>
                            <td class="font-mono">
                                <a href="{{ $appUrl }}" target="_blank" class="text-dark text-decoration-none fw-medium">
                                    {{ $appUrl }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary ps-3">Admin portal</td>
                            <td class="font-mono">
                                <a href="{{ $adminLoginUrl }}" class="text-dark text-decoration-none fw-medium">
                                    {{ $adminLoginUrl }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-secondary ps-3">Admin identifier</td>
                            <td class="font-mono text-dark">{{ $adminEmail }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary ps-3">Database target</td>
                            <td class="font-mono text-dark">{{ $databaseName }}</td>
                        </tr>
                        <tr>
                            <td class="text-secondary ps-3">Timestamp</td>
                            <td class="font-mono text-muted">{{ $completionTime }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Security Hardening Summary -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary font-mono" style="font-size: 0.76rem;">
                HARDENING STATUS
            </div>
            <div class="card-body p-3">
                <div class="row g-2 small font-mono" style="font-size: 0.78rem;">
                    <div class="col-md-6 text-secondary">
                        <i class="fa-solid fa-check text-success me-1"></i> Lock file: <code>installed.lock</code> created
                    </div>
                    <div class="col-md-6 text-secondary">
                        <i class="fa-solid fa-check text-success me-1"></i> Production mode: <code>APP_DEBUG=false</code>
                    </div>
                    <div class="col-md-6 text-secondary">
                        <i class="fa-solid fa-check text-success me-1"></i> Cipher key: <code>APP_KEY</code> verified
                    </div>
                    <div class="col-md-6 text-secondary">
                        <i class="fa-solid fa-check text-success me-1"></i> Session protection: CSRF active
                    </div>
                </div>
            </div>
        </div>

        <!-- cPanel Security Reminder -->
        <div class="p-3 bg-light border rounded-2">
            <div class="small text-secondary">
                <strong>Hosting note:</strong> The installer lock is registered at <code>storage/app/installer/installed.lock</code>. Any direct access to installer endpoints will now be redirected to the sign-in screen.
            </div>
        </div>
    </div>

    <div class="card-footer-installer justify-content-end gap-2">
        <a href="{{ $appUrl }}" class="btn btn-zen-outline">
            <span>Visit homepage</span>
        </a>
        <a href="{{ $adminLoginUrl }}" class="btn btn-zen">
            <span>Log in to admin dashboard</span>
            <i class="fa-solid fa-arrow-right" style="font-size: 0.78rem;"></i>
        </a>
    </div>
@endsection
