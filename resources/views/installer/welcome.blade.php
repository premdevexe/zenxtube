@extends('installer.layout', ['step' => 1])

@section('title', 'System Setup')

@section('content')
    <div class="card-header-installer">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h2>Installation Setup</h2>
                <p>Deployment wizard for cPanel shared hosting and Apache web servers.</p>
            </div>
            <span class="sys-tag font-mono">[MODE: INITIAL_SETUP]</span>
        </div>
    </div>

    <div class="card-body-installer">
        <!-- System Brief Box -->
        <div class="p-3 rounded-2 mb-4 bg-light border">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <div class="fw-semibold small text-dark mb-1">Target Environment Configuration</div>
                    <div class="text-secondary small">
                        Automated initialization for database schemas, encryption credentials, and production runtime settings without SSH access.
                    </div>
                </div>
                <div class="font-mono text-muted small">
                    [HOST: cPanel / Apache]
                </div>
            </div>
        </div>

        <!-- Capability Matrix -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 rounded-2 border bg-white h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-secondary pt-1">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small mb-1">Server prerequisites</div>
                            <div class="text-muted small">Verification of PHP 8.2+, 11 required extensions, and writable storage paths.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-2 border bg-white h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-secondary pt-1">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small mb-1">MySQL and MariaDB</div>
                            <div class="text-muted small">Real-time connection handshake and table creation privilege test.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-2 border bg-white h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-secondary pt-1">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small mb-1">Non-destructive execution</div>
                            <div class="text-muted small">Automatic backups of existing configuration. Preserves existing keys without wiping data.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 rounded-2 border bg-white h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="text-secondary pt-1">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small mb-1">Installation lock</div>
                            <div class="text-muted small">Generates a permanent lock upon completion to block unauthorized re-installation.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hardware & Stack Specs -->
        <div class="border rounded-2 p-3 bg-light">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small font-mono">
                <div>Framework: Laravel {{ $laravelVersion }} &bull; PHP {{ $phpVersion }}</div>
                <div>Pipeline: Multi-stage AJAX runner</div>
            </div>
        </div>
    </div>

    <div class="card-footer-installer">
        <div class="text-muted small font-mono">
            Step 1 of 6
        </div>
        <a href="{{ route('installer.requirements') }}" class="btn btn-zen">
            <span>Continue to requirements</span>
            <i class="fa-solid fa-arrow-right" style="font-size: 0.78rem;"></i>
        </a>
    </div>
@endsection
