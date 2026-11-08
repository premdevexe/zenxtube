<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Admin Dashboard') - {{ \App\SiteSetting::get('site_name', 'Zen X Download') }}</title>

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
        :root {
            --admin-primary: #0062E0;
            --admin-dark: #1a202c;
            --admin-sidebar-bg: #1e293b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            background-color: var(--admin-sidebar-bg);
            min-height: 100vh;
            color: #94a3b8;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .admin-sidebar .sidebar-brand {
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            font-weight: 800;
            font-size: 1.15rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
        }

        .admin-sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .admin-sidebar .nav-link i {
            width: 22px;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
            border-left-color: #38bdf8;
        }

        /* Topbar */
        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        /* Cards & Stat Widgets */
        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .table thead th {
            border-top: none;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            border-radius: 6px;
        }

        @media (max-width: 767.98px) {
            .admin-sidebar {
                min-height: auto;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row no-gutters">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 admin-sidebar">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand text-decoration-none d-flex align-items-center">
                    <img src="{{ \App\SiteSetting::assetPath('site_logo_white', 'images/logo-white.svg') }}" alt="{{ \App\SiteSetting::get('site_name', 'Zen X Download') }} Admin" height="32" style="max-height: 34px; width: auto; object-fit: contain;">
                </a>

                <div class="py-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin') || Request::is('admin/dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fa fa-dashboard text-info"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/downloads*') ? 'active' : '' }}" href="{{ route('admin.downloads') }}">
                                <i class="fa fa-download text-success"></i> Download Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/messages*') ? 'active' : '' }}" href="{{ route('admin.messages') }}">
                                <i class="fa fa-envelope text-warning"></i> Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/notifications*') ? 'active' : '' }}" href="{{ route('admin.notifications') }}">
                                <i class="fa fa-bell text-danger"></i> Notifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/system*') ? 'active' : '' }}" href="{{ route('admin.system') }}">
                                <i class="fa fa-cogs text-primary"></i> System & Cache
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                                <i class="fa fa-sliders text-success"></i> Site Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/profile*') ? 'active' : '' }}" href="{{ route('admin.profile') }}">
                                <i class="fa fa-user-circle text-secondary"></i> Admin Profile
                            </a>
                        </li>
                    </ul>

                    <div class="px-3 pt-4 pb-2 text-uppercase font-weight-bold d-flex align-items-center justify-content-between" style="font-size: 0.7rem; letter-spacing: 1px; color: #38bdf8;">
                        <span>API Platform</span>
                        <span class="badge badge-info px-1 py-0" style="font-size: 0.65rem;">v1 REST</span>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-success font-weight-bold" href="{{ route('admin.api.clients') }}?action=create">
                                <i class="fa fa-plus-circle text-success"></i> + Create API Client
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api') ? 'active' : '' }}" href="{{ route('admin.api.index') }}">
                                <i class="fa fa-cubes text-info"></i> API Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/settings*') ? 'active' : '' }}" href="{{ route('admin.api.settings') }}">
                                <i class="fa fa-sliders text-warning"></i> API Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/clients*') ? 'active' : '' }}" href="{{ route('admin.api.clients') }}">
                                <i class="fa fa-id-badge text-primary"></i> App Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/devices*') ? 'active' : '' }}" href="{{ route('admin.api.devices') }}">
                                <i class="fa fa-mobile text-success" style="font-size: 1.25rem;"></i> Devices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/tokens*') ? 'active' : '' }}" href="{{ route('admin.api.tokens') }}">
                                <i class="fa fa-key text-secondary"></i> Tokens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/logs*') ? 'active' : '' }}" href="{{ route('admin.api.logs') }}">
                                <i class="fa fa-list-alt text-danger"></i> API Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/api/docs*') ? 'active' : '' }}" href="{{ route('admin.api.docs') }}">
                                <i class="fa fa-book text-info"></i> Documentation
                            </a>
                        </li>
                    </ul>

                    <div class="px-3 pt-4 pb-2 text-uppercase small text-muted font-weight-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                        Quick Links
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/') }}" target="_blank">
                                <i class="fa fa-external-link text-muted"></i> View Live Site
                            </a>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('admin.logout') }}" method="post" id="logoutForm">
                                {{ csrf_field() }}
                                <a class="nav-link text-danger" href="javascript:void(0)" onclick="document.getElementById('logoutForm').submit();">
                                    <i class="fa fa-sign-out"></i> Logout
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-10 col-md-9 d-flex flex-column" style="min-height: 100vh;">
                <!-- Topbar -->
                <div class="admin-topbar d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark">@yield('page_title', 'Dashboard')</h5>
                        <small class="text-muted">{{ \App\SiteSetting::get('site_name', 'Zen X Download') }} Administration Portal</small>
                    </div>

                    <div class="d-flex align-items-center">
                        <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-primary btn-sm mr-3 font-weight-bold">
                            <i class="fa fa-globe"></i> View Website
                        </a>
                        @if(Auth::check())
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle font-weight-bold d-flex align-items-center" type="button" id="userMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-user-circle-o fa-lg text-primary mr-2"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="userMenu">
                                <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fa fa-cog mr-2"></i> Profile Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="document.getElementById('logoutForm').submit();">
                                    <i class="fa fa-sign-out mr-2"></i> Sign Out
                                </a>
                            </div>
                        </div>
                        @else
                        <a href="{{ route('admin.login') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                            <i class="fa fa-sign-in mr-1"></i> Admin Login
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Page Content Body -->
                <div class="p-3 p-md-4 flex-grow-1">
                    @php
                        try {
                            $activeTopAnnouncement = \App\Notification::where('is_banner_active', 1)
                                ->whereIn('type', ['banner', 'both'])
                                ->latest()
                                ->first();
                        } catch (\Exception $e) {
                            $activeTopAnnouncement = null;
                        }
                    @endphp

                    @if($activeTopAnnouncement)
                        <div class="alert alert-{{ $activeTopAnnouncement->banner_style ?? 'info' }} alert-dismissible fade show shadow-sm border-0 font-weight-bold mb-4" role="alert" style="border-left: 5px solid rgba(0,0,0,0.2) !important;">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="py-1">
                                    <span class="badge badge-light text-uppercase mr-2 font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Site Announcement</span>
                                    <i class="fa fa-bullhorn mr-1"></i>
                                    <strong>{{ $activeTopAnnouncement->title }}:</strong> {{ $activeTopAnnouncement->message }}
                                </div>
                                <div class="d-flex align-items-center mt-2 mt-md-0">
                                    @if($activeTopAnnouncement->link)
                                        <a href="{{ $activeTopAnnouncement->link }}" target="_blank" class="btn btn-sm btn-dark py-0 px-2 mr-2 font-weight-bold" style="font-size: 0.8rem; border-radius: 12px;">
                                            Test Link &rarr;
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.notifications') }}" class="btn btn-sm btn-outline-dark py-0 px-2 mr-3 font-weight-bold" style="font-size: 0.8rem; border-radius: 12px;">
                                        Manage
                                    </a>
                                    <button type="button" class="close position-static py-0 px-1 ml-1" data-dismiss="alert" aria-label="Close" style="font-size: 1.2rem; line-height: 1;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fa fa-exclamation-circle mr-1"></i> <strong>Please fix the following:</strong>
                            <ul class="mb-0 mt-1 pl-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>

                <!-- Admin Footer -->
                <footer class="bg-white border-top p-3 text-center text-muted small mt-auto">
                    &copy; {{ date('Y') }} <strong>{{ \App\SiteSetting::get('site_name', 'Zen X Download') }}</strong> Admin Panel &bull; Powered by Laravel {{ app()->version() }}
                </footer>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    @yield('scripts')
</body>
</html>
