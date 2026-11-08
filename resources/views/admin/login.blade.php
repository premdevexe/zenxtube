<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Login - Zen X Download</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg') }}">
    <link rel="alternate icon" href="{{ \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg') }}">

    <!-- Bootstrap 4.1.1 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }

        .login-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .login-btn {
            background: linear-gradient(135deg, #0062E0 0%, #0099ff 100%);
            border: none;
            font-weight: 700;
            border-radius: 8px;
            padding: 0.75rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 98, 224, 0.4);
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 98, 224, 0.2);
            border-color: #0062E0;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header text-center">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="d-inline-block mb-2">
                <img src="{{ \App\SiteSetting::assetPath('site_logo', 'images/logo.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }}" height="46" style="max-height: 48px; width: auto; object-fit: contain;">
            </a>
            <h5 class="font-weight-bold text-dark mb-1">Administration Portal</h5>
            <p class="text-muted small mb-0">Sign in to manage downloads, notifications, and support</p>
        </div>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success small mb-3">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger small mb-3">
                    <i class="fa fa-exclamation-circle"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="post">
                {{ csrf_field() }}

                <div class="form-group mb-3">
                    <label for="email" class="font-weight-bold small text-secondary">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fa fa-envelope-o text-muted"></i></span>
                        </div>
                        <input type="email" name="email" id="email" class="form-control border-left-0" placeholder="admin@zenxdownload.com" value="{{ old('email', 'admin@zenxdownload.com') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="password" class="font-weight-bold small text-secondary">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fa fa-lock text-muted"></i></span>
                        </div>
                        <input type="password" name="password" id="password" class="form-control border-left-0" placeholder="••••••••" value="admin123" required>
                    </div>
                </div>

                <div class="form-group d-flex justify-content-between align-items-center mb-4">
                    <div class="custom-control custom-checkbox small">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" checked>
                        <label class="custom-control-label text-muted" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block login-btn shadow-sm">
                    <i class="fa fa-sign-in mr-1"></i> Sign In to Dashboard
                </button>
            </form>

            <div class="alert alert-light border small text-muted mt-4 mb-0 text-center">
                <i class="fa fa-info-circle text-info"></i> <strong>Default Credentials:</strong><br>
                Email: <code>admin@zenxdownload.com</code> &bull; Password: <code>admin123</code>
            </div>

            <div class="text-center mt-3">
                <a href="{{ url('/') }}" class="small text-muted text-decoration-none">
                    <i class="fa fa-arrow-left"></i> Return to Zen X Download Homepage
                </a>
            </div>
        </div>
    </div>

</body>
</html>
