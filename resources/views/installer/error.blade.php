@extends('installer.layout', ['step' => 5])

@section('title', 'Installation Error')

@section('content')
    <div class="card-header-installer py-3">
        <h2>Deployment exception</h2>
        <p>The installer halted execution to prevent inconsistent state.</p>
    </div>

    <div class="card-body-installer">
        <div class="alert alert-danger mb-3 p-3 rounded-2 small font-mono">
            <div class="fw-semibold mb-1 text-danger">DIAGNOSTIC SUMMARY:</div>
            <div>
                {{ session('error') ?? ($state['last_error'] ?? 'Execution stopped due to a configuration or database error.') }}
            </div>
        </div>

        <div class="card border rounded-2 mb-3">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary font-mono" style="font-size: 0.76rem;">
                RECOVERY PROCEDURES
            </div>
            <div class="card-body p-3">
                <ol class="small text-secondary mb-0 ps-3">
                    <li class="mb-1"><strong>Database credentials:</strong> Verify in cPanel that your MySQL user has ALL PRIVILEGES assigned to the database.</li>
                    <li class="mb-1"><strong>Filesystem permissions:</strong> Ensure <code>storage/</code> and <code>bootstrap/cache/</code> have write permissions (chmod 0775 or 0755).</li>
                    <li class="mb-1"><strong>Timeout recovery:</strong> The installer maintains completed stage state in <code>storage/app/installer/installation.json</code> and resumes without repeating completed work.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card-footer-installer justify-content-end gap-2">
        <a href="{{ route('installer.database') }}" class="btn btn-zen-outline">
            <span>Review database</span>
        </a>
        <a href="{{ route('installer.install') }}" class="btn btn-zen">
            <i class="fa-solid fa-rotate-right" style="font-size: 0.78rem;"></i>
            <span>Resume installation</span>
        </a>
    </div>
@endsection
