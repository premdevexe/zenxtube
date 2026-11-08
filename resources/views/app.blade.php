@php
    $siteName       = \App\SiteSetting::get('site_name', 'Zen X Download');
    $seoTitle       = \App\SiteSetting::get('seo_title', 'Zen X Download - Fast & Free Online Video & Audio Downloader');
    $seoDescription = \App\SiteSetting::get('seo_description');
    $seoKeywords    = \App\SiteSetting::get('seo_keywords');
    $metaAuthor     = \App\SiteSetting::get('meta_author', 'Zen X Download');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', $seoTitle ?: ($siteName . ' - Fast & Free Online Video & Audio Downloader'))</title>
    @if($seoDescription)
    <meta name="description" content="{{ $seoDescription }}">
    @endif
    @if($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    @if($metaAuthor)
    <meta name="author" content="{{ $metaAuthor }}">
    @endif

    @php
        $gaId = \App\SiteSetting::get('google_analytics_id', '');
    @endphp
    @if($gaId)
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg') }}">
    <link rel="alternate icon" href="{{ \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg') }}">
    <link rel="apple-touch-icon" href="{{ \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg') }}">

    <!-- Bootstrap 4.1.1 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Global Responsive Styles -->
    <style>
        :root {
            --primary-blue: #0062E0;
            --primary-gradient: linear-gradient(135deg, #0062E0 0%, #0099ff 100%);
        }

        html, body {
            overflow-x: hidden;
            width: 100%;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #2d3748;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .main-content {
            flex: 1 0 auto;
            width: 100%;
        }

        /* Navbar & Header */
        .main-navbar {
            position: static;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background: #ffffff !important;
            transition: all 0.3s ease;
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #1a202c;
            line-height: 1.1;
        }

        .brand-sub {
            color: #2b6cb0;
        }

        .brand-tagline {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .navbar-nav .nav-link {
            font-size: 0.92rem;
            color: #4a5568 !important;
            padding: 0.55rem 0.85rem !important;
            transition: color 0.2s ease;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-item.active .nav-link {
            color: #0062E0 !important;
        }

        .download-nav-btn {
            border-radius: 20px;
            background: var(--primary-gradient);
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .download-nav-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 98, 224, 0.35) !important;
        }

        /* Responsive Hero Typography */
        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .hero-lead {
            font-size: 1.15rem;
            color: #718096;
        }

        /* Responsive Search Input Box */
        .responsive-search-box {
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #0062E0;
            background: #ffffff;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .responsive-search-box:focus-within {
            box-shadow: 0 0 0 4px rgba(0, 98, 224, 0.18) !important;
        }

        .responsive-search-input {
            height: 62px;
            font-size: 1.05rem;
            border: none;
            box-shadow: none !important;
        }

        .responsive-search-btn {
            height: 62px;
            min-width: 150px;
            background: var(--primary-gradient);
            border: none;
            font-weight: 700;
            font-size: 1rem;
            border-radius: 0;
            transition: filter 0.2s ease;
        }

        .responsive-search-btn:hover {
            filter: brightness(1.08);
        }

        /* Mobile Adjustments for Search Box (< 576px) */
        @media (max-width: 575.98px) {
            .hero-title {
                font-size: 1.75rem !important;
            }

            .hero-lead {
                font-size: 0.95rem !important;
            }

            .responsive-search-box {
                display: flex;
                flex-direction: column;
                border: none;
                background: transparent;
                box-shadow: none !important;
            }

            .responsive-search-input {
                width: 100% !important;
                height: 52px !important;
                border: 2px solid #0062E0 !important;
                border-radius: 10px !important;
                margin-bottom: 10px;
                padding: 0 16px !important;
                font-size: 0.95rem !important;
            }

            .responsive-search-box .input-group-append {
                width: 100% !important;
                margin-left: 0 !important;
            }

            .responsive-search-btn {
                width: 100% !important;
                height: 52px !important;
                border-radius: 10px !important;
                display: block !important;
            }

            .navbar-collapse {
                background: #ffffff;
                padding: 1rem 0;
                border-top: 1px solid #edf2f7;
                margin-top: 0.5rem;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem !important;
                border-radius: 8px;
            }

            .navbar-nav .nav-link:hover {
                background-color: #f7fafc;
            }

            .site-footer {
                text-align: center;
            }

            .site-footer .footer-links a {
                padding: 4px 0;
            }
        }

        /* Responsive Tables & Cards */
        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }

        .table th, .table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Footer */
        .site-footer {
            background-color: #1a202c !important;
            color: #a0aec0;
            flex-shrink: 0;
        }

        .site-footer hr {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .footer-links a {
            transition: color 0.2s ease, transform 0.2s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #ffffff !important;
            text-decoration: none;
            transform: translateX(3px);
        }
    </style>

    @yield('styles')
</head>
<body>

    @php
        $maintenanceActive = \App\SiteSetting::get('maintenance_active') === '1';
        $maintenanceMsg    = \App\SiteSetting::get('maintenance_message', 'We are performing scheduled maintenance. Please check back soon!');
        $isAuthedAdmin    = auth()->check() && (auth()->user()->is_admin ?? false);
        $allowedLinks      = array_values(array_filter(array_map('trim', explode(',', (string) \App\SiteSetting::get('maintenance_allowed_links')))));
        $uri               = url()->current();
        $isAllowedUri      = collect($allowedLinks)->filter(function ($link) use ($uri) {
            return $link !== '' && (strpos($uri, $link) !== false || (function_exists('str_starts_with') && str_starts_with($uri, $link)));
        })->isNotEmpty() || Request::is('admin*');
    @endphp

    @if($maintenanceActive && !$isAuthedAdmin && !$isAllowedUri)
        <div class="d-flex align-items-center justify-content-center bg-light min-vh-100 py-5">
            <div class="text-center p-4 max-w-md" style="max-width: 520px;">
                <div class="mb-4 d-flex align-items-center justify-content-center">
                    <img src="{{ \App\SiteSetting::assetPath('site_logo', 'images/logo.svg') }}" alt="{{ $siteName }}" height="54">
                </div>
                <h2 class="font-weight-bold text-dark mb-3"><i class="fa fa-wrench text-warning mr-2"></i> We'll Be Back Soon</h2>
                <p class="text-secondary mb-4">{{ $maintenanceMsg }}</p>
                @if(count($allowedLinks))
                    <div class="d-flex flex-wrap justify-content-center mb-2">
                        @foreach($allowedLinks as $allowed)
                            <a href="{{ $allowed }}" class="btn btn-outline-primary btn-sm mx-1 my-1 font-weight-bold">{{ parse_url($allowed, PHP_URL_HOST) ?: $allowed }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else

    <!-- Header Navigation -->
    @include('partials.header')

    <!-- Main Page Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
      let beamsClient = null;
      try {
        beamsClient = new PusherPushNotifications.Client({
          instanceId: '@php echo \App\SiteSetting::get('beams_instance_id', '9497a1ea-8ab7-45e1-9c1e-e5af01309442'); @endphp',
        });
        window.beamsClient = beamsClient;
      } catch (err) {
        console.warn('Pusher Beams initialization skipped:', err);
      }

      function enablePushNotifications() {
        if (!("Notification" in window)) {
          alert("Desktop notifications are not supported by this browser.");
          return;
        }

        Notification.requestPermission().then(function(permission) {
          updateNotifButtonUI();
          if (permission === 'granted') {
            try {
              new Notification('Notifications Enabled! 🎉', {
                body: 'You are subscribed to instant updates and downloads from Zen X Download.',
                icon: '{{ url("favicon.ico") }}'
              });
            } catch (e) {}

            if (beamsClient) {
              beamsClient.start()
                .then(() => beamsClient.addDeviceInterest('hello'))
                .then(() => console.log('Pusher Beams subscribed!'))
                .catch(e => console.log('Beams registration note:', e));
            }
          } else if (permission === 'denied') {
            alert('Notifications were blocked. You can unblock them anytime in your browser URL bar settings.');
          }
        });
      }

      function updateNotifButtonUI() {
        const btn = document.getElementById('enableNotifBtn');
        const text = document.getElementById('enableNotifText');
        const icon = document.getElementById('enableNotifIcon');
        if (!btn || !("Notification" in window)) return;

        if (Notification.permission === 'granted') {
          btn.className = 'btn btn-outline-success btn-sm px-2 shadow-sm font-weight-bold d-block d-lg-inline-block text-center';
          if (text) text.innerText = 'Active';
          if (icon) icon.className = 'fa fa-check-circle text-success';
          btn.title = 'Desktop Notifications are Enabled';
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        updateNotifButtonUI();

        @php
          try {
            $livePushNotif = \App\Notification::whereIn('type', ['push', 'both'])->latest()->first();
          } catch (\Exception $e) {
            $livePushNotif = null;
          }
        @endphp

        @if($livePushNotif)
          const liveNotif = {!! json_encode(['id' => $livePushNotif->id, 'title' => $livePushNotif->title, 'message' => $livePushNotif->message, 'link' => $livePushNotif->link], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

          if (liveNotif && ("Notification" in window) && Notification.permission === 'granted') {
            const seenKey = 'zenx_push_seen_' + liveNotif.id;
            if (!localStorage.getItem(seenKey)) {
              localStorage.setItem(seenKey, '1');
              try {
                const n = new Notification(liveNotif.title, {
                  body: liveNotif.message,
                  icon: '{{ url("favicon.ico") }}'
                });
                n.onclick = function() {
                  window.focus();
                  if (liveNotif.link) window.open(liveNotif.link, '_blank');
                };
              } catch (e) {}
            }
          }
        @endif
      });
    </script>

    @endif

    @yield('scripts')
</body>
</html>
