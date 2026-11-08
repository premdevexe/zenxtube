<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Installation Wizard') — {{ config('installer.name', 'Zen X Download') }}</title>

    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            --surface-bg: #f5f6f8;
            --surface-card: #ffffff;
            --surface-raised: #f9fafb;
            --surface-inset: #f3f4f6;
            --border-hairline: rgba(17, 24, 39, 0.08);
            --border-medium: rgba(17, 24, 39, 0.14);
            
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;

            --accent: #1e3a8a;
            --accent-hover: #172554;
            --accent-subtle: #eff6ff;

            --status-success: #15803d;
            --status-success-bg: #f0fdf4;
            --status-warning: #b45309;
            --status-warning-bg: #fffbeb;
            --status-danger: #b91c1c;
            --status-danger-bg: #fef2f2;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--surface-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace !important;
            font-feature-settings: "tnum" 1;
        }

        /* Top Header */
        .installer-navbar {
            background: var(--surface-card);
            border-bottom: 1px solid var(--border-hairline);
            padding: 14px 0;
        }

        .installer-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.05rem;
        }

        .brand-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--surface-inset);
            border: 1px solid var(--border-medium);
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }

        .sys-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: var(--text-muted);
            background: var(--surface-raised);
            border: 1px solid var(--border-hairline);
            padding: 4px 8px;
            border-radius: 6px;
        }

        /* Stepper Navigation */
        .installer-stepper {
            background: var(--surface-card);
            border-bottom: 1px solid var(--border-hairline);
            padding: 20px 0 16px;
        }

        .steps-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .steps-progress-line {
            position: absolute;
            top: 17px;
            left: 28px;
            right: 28px;
            height: 2px;
            background: var(--border-hairline);
            z-index: 1;
        }

        .steps-progress-fill {
            height: 100%;
            background: var(--text-primary);
            transition: width 0.25s ease;
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }

        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--surface-card);
            border: 1px solid var(--border-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--text-muted);
            transition: all 0.2s ease;
        }

        .step-item.active .step-circle {
            background: var(--text-primary);
            border-color: var(--text-primary);
            color: #ffffff;
        }

        .step-item.completed .step-circle {
            background: var(--surface-raised);
            border-color: var(--border-medium);
            color: var(--status-success);
        }

        a.step-item.completed {
            cursor: pointer;
        }

        a.step-item.completed:hover .step-circle {
            background: var(--text-primary);
            border-color: var(--text-primary);
            color: #ffffff;
            transform: scale(1.08);
        }

        a.step-item.completed:hover .step-label {
            color: var(--text-primary);
        }

        .step-label {
            font-size: 0.76rem;
            font-weight: 500;
            margin-top: 6px;
            color: var(--text-muted);
            text-align: center;
            white-space: nowrap;
        }

        .step-item.active .step-label {
            color: var(--text-primary);
            font-weight: 600;
        }

        .step-item.completed .step-label {
            color: var(--text-secondary);
        }

        /* Main Card */
        .installer-main {
            flex: 1 0 auto;
            padding: 36px 0 54px;
        }

        .installer-card {
            background: var(--surface-card);
            border: 1px solid var(--border-hairline);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .card-header-installer {
            padding: 24px 32px 18px;
            border-bottom: 1px solid var(--border-hairline);
            background: var(--surface-card);
        }

        .card-header-installer h1, .card-header-installer h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 4px;
            letter-spacing: -0.02em;
        }

        .card-header-installer p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.88rem;
        }

        .card-body-installer {
            padding: 32px;
        }

        .card-footer-installer {
            padding: 16px 32px;
            background: var(--surface-raised);
            border-top: 1px solid var(--border-hairline);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Buttons */
        .btn-zen {
            background-color: var(--text-primary);
            border: 1px solid var(--text-primary);
            color: #ffffff;
            font-weight: 500;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .btn-zen:hover, .btn-zen:focus {
            background-color: #000000;
            border-color: #000000;
            color: #ffffff;
        }

        .btn-zen-outline {
            background: var(--surface-card);
            border: 1px solid var(--border-medium);
            color: var(--text-primary);
            font-weight: 500;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .btn-zen-outline:hover {
            background: var(--surface-inset);
            border-color: rgba(17, 24, 39, 0.25);
            color: var(--text-primary);
        }

        /* Badges */
        .badge-pass {
            background-color: var(--status-success-bg);
            color: var(--status-success);
            border: 1px solid rgba(21, 128, 61, 0.2);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-fail {
            background-color: var(--status-danger-bg);
            color: var(--status-danger);
            border: 1px solid rgba(185, 28, 28, 0.2);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 500;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Form Controls */
        .form-label {
            font-weight: 500;
            font-size: 0.84rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .form-control, .form-select {
            border: 1px solid var(--border-medium);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.88rem;
            color: var(--text-primary);
            background-color: var(--surface-card);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--text-primary);
            box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.08);
            outline: none;
        }

        .input-group-text {
            background-color: var(--surface-raised);
            border: 1px solid var(--border-medium);
            color: var(--text-muted);
            border-radius: 8px;
            font-size: 0.88rem;
        }

        .form-text {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Footer */
        .installer-footer {
            background: var(--surface-card);
            border-top: 1px solid var(--border-hairline);
            padding: 16px 0;
            color: var(--text-muted);
            font-size: 0.78rem;
        }

        @media (max-width: 768px) {
            .steps-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                padding-bottom: 8px;
            }
            .step-label {
                font-size: 0.72rem;
            }
            .card-body-installer {
                padding: 20px 16px;
            }
            .card-header-installer, .card-footer-installer {
                padding: 16px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Header Navbar -->
    <header class="installer-navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('installer.welcome') }}" class="installer-brand">
                    <span class="brand-icon-box"><i class="fa-solid fa-terminal"></i></span>
                    <span>{{ config('installer.name', 'Zen X Download') }}</span>
                    <span class="sys-tag ms-1">v{{ config('installer.version', '2.5.0') }}</span>
                </a>
                <div class="d-flex align-items-center gap-2">
                    <span class="sys-tag">Laravel {{ app()->version() }}</span>
                    <span class="sys-tag">PHP {{ PHP_VERSION }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Stepper Navigation -->
    @php
        $currentStep = $step ?? 1;
        $steps = [
            1 => ['title' => 'Welcome', 'route' => 'installer.welcome'],
            2 => ['title' => 'Requirements', 'route' => 'installer.requirements'],
            3 => ['title' => 'Database', 'route' => 'installer.database'],
            4 => ['title' => 'Configuration', 'route' => 'installer.configuration'],
            5 => ['title' => 'Installation', 'route' => 'installer.install'],
            6 => ['title' => 'Complete', 'route' => 'installer.complete'],
        ];
        $progressPct = max(0, min(100, (($currentStep - 1) / 5) * 100));
    @endphp

    <section class="installer-stepper">
        <div class="container">
            <div class="steps-wrapper">
                <div class="steps-progress-line">
                    <div class="steps-progress-fill" style="width: {{ $progressPct }}%;"></div>
                </div>

                @foreach($steps as $idx => $stepData)
                    @if($idx < $currentStep)
                        <a href="{{ route($stepData['route']) }}" class="step-item completed text-decoration-none" title="Return to {{ $stepData['title'] }}">
                            <div class="step-circle">
                                <i class="fa-solid fa-check" style="font-size: 0.78rem;"></i>
                            </div>
                            <span class="step-label">{{ $stepData['title'] }}</span>
                        </a>
                    @elseif($idx === $currentStep)
                        <div class="step-item active">
                            <div class="step-circle">
                                <span>{{ $idx }}</span>
                            </div>
                            <span class="step-label">{{ $stepData['title'] }}</span>
                        </div>
                    @else
                        <div class="step-item disabled" style="opacity: 0.55;">
                            <div class="step-circle">
                                <span>{{ $idx }}</span>
                            </div>
                            <span class="step-label">{{ $stepData['title'] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- Main Installer Card Content -->
    <main class="installer-main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">

                    <!-- Flash messages -->
                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3 small rounded-3" role="alert">
                            <i class="fa-solid fa-check me-2 text-success"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger d-flex align-items-center mb-3 py-2 px-3 small rounded-3" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger mb-3 p-3 small rounded-3" role="alert">
                            <div class="fw-semibold mb-1">Configuration issues detected:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="installer-card">
                        @yield('content')
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="installer-footer">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div>
                    <strong>Zen X System Installer</strong> &bull; Non-destructive Apache &amp; cPanel Deployment Pipeline
                </div>
                <div class="font-mono text-muted">
                    <span>[STATUS: READY]</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    @yield('scripts')
</body>
</html>
