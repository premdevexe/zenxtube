@extends('admin.layout')

@section('title', 'Send Notification')
@section('page_title', 'Broadcast & Notifications Center')

@section('styles')
<style>
/* Simulated Desktop Push Notification */
.push-preview-box {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    max-width: 380px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
}

.push-preview-header {
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.72rem;
    color: #64748b;
}

.push-preview-body {
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
}

.push-preview-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    margin-right: 12px;
    flex-shrink: 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.push-preview-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
    line-height: 1.25;
}

.push-preview-message {
    font-size: 0.78rem;
    color: #475569;
    margin-bottom: 0;
    line-height: 1.35;
    word-break: break-word;
}
</style>
@endsection

@section('content')

    <!-- Top KPI Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem;">Total Broadcasts</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $totalSent }}</h3>
                        <small class="text-muted">Push & on-site banners</small>
                    </div>
                    <div class="stat-icon bg-light text-primary">
                        <i class="fa fa-bell-o"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem;">Active Site Banner</span>
                        <h3 class="font-weight-bold text-dark mb-0 mt-1">{{ $activeBanners }}</h3>
                        <small class="{{ $activeBanners > 0 ? 'text-success' : 'text-muted' }}">
                            <i class="fa fa-circle"></i> {{ $activeBanners > 0 ? 'Displaying to visitors' : 'No active banner' }}
                        </small>
                    </div>
                    <div class="stat-icon bg-light text-success">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem;">Target Interest</span>
                        <h4 class="font-weight-bold text-dark mb-0 mt-1">"hello"</h4>
                        <small class="text-info"><i class="fa fa-users"></i> Subscribed devices</small>
                    </div>
                    <div class="stat-icon bg-light text-info">
                        <i class="fa fa-bullseye"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-12 mb-3">
            <div class="card stat-card bg-white p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 0.72rem;">Pusher Beams</span>
                        <h5 class="font-weight-bold mb-0 mt-1 {{ $beamsConfigured ? 'text-success' : 'text-warning' }}">
                            {{ $beamsConfigured ? 'Connected' : 'Frontend Client' }}
                        </h5>
                        <small class="text-muted text-truncate d-block" style="max-width: 140px;" title="{{ $beamsInstanceId }}">
                            ID: {{ substr($beamsInstanceId, 0, 8) }}...
                        </small>
                    </div>
                    <div class="stat-icon bg-light {{ $beamsConfigured ? 'text-success' : 'text-warning' }}">
                        <i class="fa fa-wifi"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Form & Live Preview -->
    <div class="row">
        <!-- Composer Form -->
        <div class="col-lg-7 col-12 mb-4">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-paper-plane text-primary mr-1"></i> Compose New Notification
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.notifications.send') }}" method="post" id="notificationForm">
                        {{ csrf_field() }}

                        <div class="form-group mb-3">
                            <label for="title" class="font-weight-bold small text-secondary">Notification Title</label>
                            <input type="text" 
                                   name="title" 
                                   id="notifTitleInput" 
                                   class="form-control" 
                                   placeholder="e.g. 4K & MP3 Speed Boosted!" 
                                   value="{{ old('title', '🚀 Download Speeds Boosted!') }}" 
                                   required 
                                   maxlength="150"
                                   oninput="updatePreview()">
                        </div>

                        <div class="form-group mb-3">
                            <label for="message" class="font-weight-bold small text-secondary">Notification Message / Body</label>
                            <textarea name="message" 
                                      id="notifMessageInput" 
                                      rows="3" 
                                      class="form-control" 
                                      placeholder="Write your announcement or push message..." 
                                      required 
                                      maxlength="1000"
                                      oninput="updatePreview()">We have upgraded our video downloader engine with 8 parallel streams. Enjoy instant downloads!</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="type" class="font-weight-bold small text-secondary">Delivery Channel</label>
                                <select name="type" id="notifTypeSelect" class="form-control" onchange="updatePreview()">
                                    <option value="both" selected>Both (Push & On-Site Banner)</option>
                                    <option value="push">Browser Web Push Only</option>
                                    <option value="banner">On-Site Banner Only</option>
                                </select>
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="banner_style" class="font-weight-bold small text-secondary">Banner Theme / Style</label>
                                <select name="banner_style" id="notifStyleSelect" class="form-control" onchange="updatePreview()">
                                    <option value="info" selected>Info (Blue Accent)</option>
                                    <option value="success">Success (Green Accent)</option>
                                    <option value="warning">Warning (Yellow Accent)</option>
                                    <option value="danger">Urgent / Alert (Red Accent)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="link" class="font-weight-bold small text-secondary">Action URL / Link (Optional)</label>
                                <input type="url" 
                                       name="link" 
                                       id="notifLinkInput" 
                                       class="form-control" 
                                       placeholder="{{ url('/') }}" 
                                       value="{{ old('link', url('/')) }}"
                                       oninput="updatePreview()">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="target_interest" class="font-weight-bold small text-secondary">Pusher Beams Audience Interest</label>
                                <input type="text" 
                                       name="target_interest" 
                                       id="notifInterestInput" 
                                       class="form-control" 
                                       value="{{ old('target_interest', 'hello') }}" 
                                       required>
                                <small class="text-muted">Matches <code>addDeviceInterest('hello')</code> in app</small>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 text-right">
                            <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-4 shadow-sm" style="background: linear-gradient(135deg, #0062E0 0%, #0099ff 100%); border: none;">
                                <i class="fa fa-send mr-1"></i> Broadcast Notification Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Live Preview Panel -->
        <div class="col-lg-5 col-12 mb-4">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="fa fa-desktop text-success mr-1"></i> Live Real-Time Previews
                    </h6>
                </div>
                <div class="card-body p-4 bg-light d-flex flex-column justify-content-center">
                    
                    <!-- 1. Simulated Browser Push Notification -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase small font-weight-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                                <i class="fa fa-chrome mr-1"></i> Desktop / Browser Push
                            </span>
                            <span id="browserPushBadge" class="badge badge-light border">Checking...</span>
                        </div>
                        
                        <div class="push-preview-box">
                            <div class="push-preview-header">
                                <span><i class="fa fa-globe text-primary mr-1"></i> 127.0.0.1:8000</span>
                                <span>Just now</span>
                            </div>
                            <div class="push-preview-body">
                                <div class="push-preview-icon d-flex align-items-center justify-content-center bg-primary text-white font-weight-bold">
                                    <svg width="28" height="28" viewBox="0 0 48 48" fill="none">
                                        <rect width="48" height="48" rx="10" fill="#0062E0" />
                                        <path d="M24 11V28M24 28L16 20M24 28L32 20" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
                                        <path d="M13 34H35" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <h6 class="push-preview-title" id="previewPushTitle">🚀 Download Speeds Boosted!</h6>
                                    <p class="push-preview-message" id="previewPushBody">We have upgraded our video downloader engine with 8 parallel streams. Enjoy instant downloads!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Instant Test Desktop Push Button -->
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm font-weight-bold" onclick="testBrowserPush()" style="border-radius: 20px;">
                                <i class="fa fa-bell mr-1"></i> Pop-Up Test Push On My Screen
                            </button>
                            <div id="browserPushHelp" class="small text-muted mt-1" style="font-size: 0.78rem;">
                                Click above to test if your browser displays OS desktop notifications.
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- 2. Simulated On-Site Header Banner -->
                    <div>
                        <span class="text-uppercase small font-weight-bold text-muted d-block mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="fa fa-bullhorn mr-1"></i> Simulated On-Site Top Banner
                        </span>

                        <div id="previewBannerContainer" class="alert alert-info py-2 px-3 text-center border-0 shadow-sm rounded mb-0" style="font-size: 0.85rem;">
                            <i class="fa fa-bullhorn mr-1"></i>
                            <strong id="previewBannerTitle">🚀 Download Speeds Boosted!:</strong>
                            <span id="previewBannerBody">We have upgraded our video downloader engine with 8 parallel streams.</span>
                            <a href="#" class="btn btn-sm btn-dark ml-2 py-0 px-2 font-weight-bold" style="font-size: 0.75rem; border-radius: 10px;">
                                Learn More &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- History / Sent Notifications Table -->
    <div class="card border-0 shadow-sm bg-white mt-2">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="font-weight-bold text-dark mb-0">
                <i class="fa fa-history text-secondary mr-1"></i> Sent Notifications & Banners History
            </h6>
            <span class="badge badge-light border font-weight-bold">{{ $notifications->total() }} Records</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Notification Content</th>
                            <th>Channel</th>
                            <th>Audience</th>
                            <th>On-Site Banner</th>
                            <th>Date Dispatched</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $item)
                            <tr>
                                <td class="align-middle text-muted small font-weight-bold">#{{ $item->id }}</td>
                                <td class="align-middle">
                                    <strong class="text-dark d-block">{{ $item->title }}</strong>
                                    <span class="text-muted small d-block text-truncate" style="max-width: 380px;">{{ $item->message }}</span>
                                    @if($item->link)
                                        <small><a href="{{ $item->link }}" target="_blank" class="text-primary"><i class="fa fa-external-link"></i> {{ $item->link }}</a></small>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($item->type === 'both')
                                        <span class="badge badge-primary font-weight-bold">Push & Banner</span>
                                    @elseif($item->type === 'push')
                                        <span class="badge badge-info font-weight-bold">Push Only</span>
                                    @else
                                        <span class="badge badge-secondary font-weight-bold">Banner Only</span>
                                    @endif
                                </td>
                                <td class="align-middle small">
                                    <code>{{ $item->target_interest }}</code>
                                </td>
                                <td class="align-middle">
                                    @if($item->is_banner_active)
                                        <span class="badge badge-success font-weight-bold"><i class="fa fa-check"></i> Active</span>
                                    @else
                                        <span class="badge badge-light border text-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="align-middle small text-muted">
                                    {{ $item->created_at ? $item->created_at->format('M d, Y H:i') : '' }}
                                </td>
                                <td class="align-middle text-right">
                                    @if(in_array($item->type, ['banner', 'both']))
                                        <form action="{{ route('admin.notifications.toggle', $item->id) }}" method="post" class="d-inline">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm {{ $item->is_banner_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="Toggle On-Site Banner">
                                                <i class="fa {{ $item->is_banner_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                {{ $item->is_banner_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.notifications.delete', $item->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this notification record?')">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa fa-bell-slash-o fa-3x mb-3 d-block text-secondary"></i>
                                    <h5>No notifications broadcasted yet</h5>
                                    <p class="small mb-0">Use the form above to compose and send your first notification!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

@endsection

@section('scripts')
<script>
function updatePreview() {
    const title = document.getElementById('notifTitleInput').value || 'Notification Title';
    const message = document.getElementById('notifMessageInput').value || 'Notification message goes here...';
    const style = document.getElementById('notifStyleSelect').value || 'info';

    // Update Push Preview
    document.getElementById('previewPushTitle').innerText = title;
    document.getElementById('previewPushBody').innerText = message;

    // Update Banner Preview
    document.getElementById('previewBannerTitle').innerText = title + ':';
    document.getElementById('previewBannerBody').innerText = ' ' + message;
    
    // Update Banner Class
    const banner = document.getElementById('previewBannerContainer');
    banner.className = 'alert alert-' + style + ' py-2 px-3 text-center border-0 shadow-sm rounded mb-0 font-weight-bold';
}

function checkPushPermissionStatus() {
    const badge = document.getElementById('browserPushBadge');
    const help = document.getElementById('browserPushHelp');
    if (!("Notification" in window)) {
        if (badge) {
            badge.className = 'badge badge-danger';
            badge.innerText = 'Not Supported';
        }
        if (help) help.innerText = 'Your browser does not support desktop notifications.';
        return;
    }

    if (Notification.permission === 'granted') {
        if (badge) {
            badge.className = 'badge badge-success';
            badge.innerText = 'Permission Granted';
        }
        if (help) help.innerHTML = '<span class="text-success"><i class="fa fa-check"></i> Notifications allowed. Ready to receive instant desktop alerts.</span>';
    } else if (Notification.permission === 'denied') {
        if (badge) {
            badge.className = 'badge badge-danger';
            badge.innerText = 'Blocked in Browser';
        }
        if (help) help.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Notifications blocked in browser settings. Click the lock icon in the address bar to allow.</span>';
    } else {
        if (badge) {
            badge.className = 'badge badge-warning';
            badge.innerText = 'Needs Permission';
        }
        if (help) help.innerText = 'Click "Send Test Push" above and choose "Allow" to enable desktop alerts.';
    }
}

function testBrowserPush() {
    const title = document.getElementById('notifTitleInput').value || '{{ \App\SiteSetting::get("site_name", "Zen X Download") }} Alert';
    const message = document.getElementById('notifMessageInput').value || 'This is how your instant browser notification appears!';
    const link = document.getElementById('notifLinkInput').value || window.location.origin;

    triggerNativeBrowserPush(title, message, link);
}

function triggerNativeBrowserPush(title, message, link) {
    if (!("Notification" in window)) {
        alert("Desktop notifications are not supported in this browser.");
        return;
    }

    if (Notification.permission === "granted") {
        createAndShowNotification(title, message, link);
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
            checkPushPermissionStatus();
            if (permission === "granted") {
                createAndShowNotification(title, message, link);
            } else {
                alert("Please click 'Allow' when the browser asks for Notification permission.");
            }
        });
    } else {
        alert("Desktop notifications are blocked for 127.0.0.1 in your browser settings. Please click the lock/settings icon next to your URL bar and set Notifications to 'Allow'.");
    }
}

function createAndShowNotification(title, message, link) {
    try {
        const notif = new Notification(title, {
            body: message,
            icon: '{{ url("favicon.ico") }}'
        });
        notif.onclick = function() {
            window.focus();
            if (link) window.open(link, '_blank');
        };
    } catch(e) {
        console.error("Browser notification error:", e);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    checkPushPermissionStatus();

    @if(session('dispatched_notification'))
        const dispatched = @json(session('dispatched_notification'));
        if (["push", "both"].includes(dispatched.type)) {
            triggerNativeBrowserPush(dispatched.title, dispatched.message, dispatched.link);
        }
    @endif
});
</script>
@endsection

