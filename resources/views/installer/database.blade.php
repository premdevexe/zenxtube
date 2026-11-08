@extends('installer.layout', ['step' => 3])

@section('title', 'Database Connection')

@section('content')
    <div class="card-header-installer">
        <h2>Database connection</h2>
        <p>Configure MySQL or MariaDB connection credentials. Standard cPanel user credentials supported.</p>
    </div>

    <form method="POST" action="{{ route('installer.database.save') }}" id="databaseForm">
        @csrf

        <div class="card-body-installer">
            <!-- cPanel Helper Note -->
            <div class="p-3 bg-light border rounded-2 mb-4">
                <div class="d-flex align-items-start gap-2 small text-secondary">
                    <span class="font-mono text-dark fw-semibold">[CPANEL NOTE]</span>
                    <div>
                        Create the MySQL database and user in your cPanel dashboard first (e.g. <code>cpuser_zenx</code>), and ensure <strong>ALL PRIVILEGES</strong> are assigned to the user.
                    </div>
                </div>
            </div>

            <!-- Dynamic Feedback Alert -->
            <div id="testAlert" class="alert d-none mb-4 py-2 px-3 rounded-2 small" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <span id="testIcon"></span>
                    <span id="testMessage" class="font-mono"></span>
                </div>
            </div>

            <div class="row g-3">
                <!-- Host -->
                <div class="col-md-8">
                    <label for="host" class="form-label">Database host <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-mono" id="host" name="host" value="{{ old('host', $current['host'] ?? '127.0.0.1') }}" required placeholder="127.0.0.1 or localhost">
                    <div class="form-text">Defaults to <code>127.0.0.1</code> or <code>localhost</code>.</div>
                </div>

                <!-- Port -->
                <div class="col-md-4">
                    <label for="port" class="form-label">Database port <span class="text-danger">*</span></label>
                    <input type="number" class="form-control font-mono" id="port" name="port" value="{{ old('port', $current['port'] ?? '3306') }}" required placeholder="3306">
                    <div class="form-text">Default port is <code>3306</code>.</div>
                </div>

                <!-- Database Name -->
                <div class="col-md-12">
                    <label for="database" class="form-label">Database name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-mono" id="database" name="database" value="{{ old('database', $current['database'] ?? '') }}" required placeholder="e.g. cpuser_zenx">
                </div>

                <!-- Username -->
                <div class="col-md-6">
                    <label for="username" class="form-label">Database username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-mono" id="username" name="username" value="{{ old('username', $current['username'] ?? 'root') }}" required placeholder="e.g. cpuser_zenxuser">
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label for="password" class="form-label">Database password</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-mono" id="password" name="password" value="{{ old('password', $current['password'] ?? '') }}" placeholder="Enter password">
                        <button type="button" class="btn btn-outline-secondary" id="togglePasswordBtn">
                            <i class="fa-regular fa-eye" id="togglePasswordIcon" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>
                    <div class="form-text">Leave blank if root has no password on local development.</div>
                </div>
            </div>

            <!-- Action: Test Connection -->
            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                <button type="button" id="testBtn" class="btn btn-zen-outline">
                    <i class="fa-solid fa-plug text-secondary" style="font-size: 0.78rem;"></i>
                    <span>Test connection</span>
                </button>
            </div>
        </div>

        <div class="card-footer-installer">
            <a href="{{ route('installer.requirements') }}" class="btn btn-zen-outline">
                <i class="fa-solid fa-arrow-left" style="font-size: 0.78rem;"></i>
                <span>Previous</span>
            </a>

            <button type="submit" class="btn btn-zen" id="submitBtn">
                <span>Save and continue</span>
                <i class="fa-solid fa-arrow-right" style="font-size: 0.78rem;"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    toggleBtn.addEventListener('click', function() {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    const testBtn = document.getElementById('testBtn');
    const testAlert = document.getElementById('testAlert');
    const testIcon = document.getElementById('testIcon');
    const testMessage = document.getElementById('testMessage');

    testBtn.addEventListener('click', function() {
        const host = document.getElementById('host').value.trim();
        const port = document.getElementById('port').value.trim();
        const database = document.getElementById('database').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!host || !database || !username) {
            testAlert.className = 'alert alert-warning py-2 px-3 rounded-2 small mb-4';
            testIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning"></i>';
            testMessage.textContent = 'Host, database name, and username are required.';
            testAlert.classList.remove('d-none');
            return;
        }

        testBtn.disabled = true;
        const origHtml = testBtn.innerHTML;
        testBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin me-1"></i> Testing...';
        testAlert.classList.add('d-none');

        fetch("{{ route('installer.database.test') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ host, port, database, username, password })
        })
        .then(res => res.json())
        .then(data => {
            testBtn.disabled = false;
            testBtn.innerHTML = origHtml;

            if (data.success) {
                testAlert.className = 'alert alert-success py-2 px-3 rounded-2 small mb-4';
                testIcon.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                testMessage.textContent = data.message + (data.server_version ? ' [Server: ' + data.server_version + ']' : '');
                testAlert.classList.remove('d-none');
            } else {
                testAlert.className = 'alert alert-danger py-2 px-3 rounded-2 small mb-4';
                testIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i>';
                testMessage.textContent = data.message || 'Database connection failed.';
                testAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            testBtn.disabled = false;
            testBtn.innerHTML = origHtml;
            testAlert.className = 'alert alert-danger py-2 px-3 rounded-2 small mb-4';
            testIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-danger"></i>';
            testMessage.textContent = 'Connection test failed: ' + err.message;
            testAlert.classList.remove('d-none');
        });
    });
</script>
@endsection
