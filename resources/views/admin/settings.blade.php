@extends('admin.layout')

@section('title', 'Site Settings')
@section('page_title', 'Site Settings')

@section('content')

    <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data" id="siteSettingsForm">
        {{ csrf_field() }}

        <div class="row">
            <!-- ============ GENERAL ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-cog text-primary mr-1"></i> General</h6>
                        <small class="text-muted">Core site identity</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label for="site_name" class="font-weight-bold small text-secondary">Site Name</label>
                            <input type="text" name="site_name" id="site_name" class="form-control"
                                   value="{{ old('site_name', $settings['site_name']) }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="site_tagline" class="font-weight-bold small text-secondary">Tagline / Badge</label>
                            <input type="text" name="site_tagline" id="site_tagline" class="form-control"
                                   value="{{ old('site_tagline', $settings['site_tagline']) }}"
                                   placeholder="Short hero badge text">
                        </div>

                        <div class="form-group mb-0">
                            <label for="support_email" class="font-weight-bold small text-secondary">Support Email</label>
                            <input type="email" name="support_email" id="support_email" class="form-control"
                                   value="{{ old('support_email', $settings['support_email']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ SEO ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-search text-success mr-1"></i> SEO & Meta</h6>
                        <small class="text-muted">Search engine description</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-3">
                            <label for="seo_title" class="font-weight-bold small text-secondary">Homepage Title ( &lt;title&gt; )</label>
                            <input type="text" name="seo_title" id="seo_title" class="form-control"
                                   value="{{ old('seo_title', $settings['seo_title']) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label for="seo_description" class="font-weight-bold small text-secondary">Meta Description</label>
                            <textarea name="seo_description" id="seo_description" rows="3" class="form-control">{{ old('seo_description', $settings['seo_description']) }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label for="seo_keywords" class="font-weight-bold small text-secondary">Meta Keywords</label>
                            <input type="text" name="seo_keywords" id="seo_keywords" class="form-control"
                                   value="{{ old('seo_keywords', $settings['seo_keywords']) }}">
                        </div>

                        <div class="form-group mb-0">
                            <label for="meta_author" class="font-weight-bold small text-secondary">Meta Author</label>
                            <input type="text" name="meta_author" id="meta_author" class="form-control"
                                   value="{{ old('meta_author', $settings['meta_author']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ FEATURES ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-bolt text-warning mr-1"></i> Featured Platforms</h6>
                        <small class="text-muted">Homepage badge toggles</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group">
                            <div class="d-flex flex-wrap">
                                @foreach([
                                    ['feat_youtube','YouTube','fa-youtube-play','text-danger'],
                                    ['feat_facebook','Facebook','fa-facebook-official','text-primary'],
                                    ['feat_instagram','Instagram','fa-instagram','text-danger'],
                                    ['feat_twitter','Twitter / X','fa-twitter','text-info'],
                                    ['feat_vimeo','Vimeo','fa-vimeo','text-info'],
                                    ['feat_mp3','MP3 Audio','fa-music','text-warning'],
                                    ['feat_mp4_4k','1080p / 4K MP4','fa-film','text-success'],
                                ] as [$key, $label, $icon, $color])
                                    <div class="custom-control custom-checkbox mr-3 mb-2">
                                        <input type="checkbox" class="custom-control-input" id="{{ $key }}" name="{{ $key }}"
                                               value="1" @if(old($key, $settings[$key]) === '1') checked @endif>
                                        <label class="custom-control-label small font-weight-bold" for="{{ $key }}">
                                            <i class="fa {{ $icon }} {{ $color }} mr-1"></i> {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-2">Unchecked platforms are hidden from the homepage "Supported Platforms" row.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ SOCIAL & ANALYTICS ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-link text-info mr-1"></i> Social & Analytics</h6>
                        <small class="text-muted">Links, tracking & push</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="social_facebook" class="font-weight-bold small text-secondary">Facebook URL</label>
                                <input type="url" name="social_facebook" id="social_facebook" class="form-control"
                                       value="{{ old('social_facebook', $settings['social_facebook']) }}" placeholder="https://facebook.com/...">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="social_twitter" class="font-weight-bold small text-secondary">Twitter / X URL</label>
                                <input type="url" name="social_twitter" id="social_twitter" class="form-control"
                                       value="{{ old('social_twitter', $settings['social_twitter']) }}" placeholder="https://twitter.com/...">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="social_youtube" class="font-weight-bold small text-secondary">YouTube URL</label>
                                <input type="url" name="social_youtube" id="social_youtube" class="form-control"
                                       value="{{ old('social_youtube', $settings['social_youtube']) }}" placeholder="https://youtube.com/...">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="social_instagram" class="font-weight-bold small text-secondary">Instagram URL</label>
                                <input type="url" name="social_instagram" id="social_instagram" class="form-control"
                                       value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="https://instagram.com/...">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="google_analytics_id" class="font-weight-bold small text-secondary">Google Analytics ID</label>
                            <input type="text" name="google_analytics_id" id="google_analytics_id" class="form-control"
                                   value="{{ old('google_analytics_id', $settings['google_analytics_id']) }}" placeholder="G-XXXXXXXXXX or UA-...">
                        </div>

                        <hr class="my-3 border-secondary">
                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="beams_instance_id" class="font-weight-bold small text-secondary">Pusher Beams Instance ID</label>
                                <input type="text" name="beams_instance_id" id="beams_instance_id" class="form-control"
                                       value="{{ old('beams_instance_id', $settings['beams_instance_id']) }}">
                            </div>
                            <div class="col-md-6 col-12 mb-3">
                                <label for="beams_target_interest" class="font-weight-bold small text-secondary">Default Push Interest</label>
                                <input type="text" name="beams_target_interest" id="beams_target_interest" class="form-control"
                                       value="{{ old('beams_target_interest', $settings['beams_target_interest']) }}">
                            </div>
                        </div>
                        <small class="text-muted d-block">Web push &amp; PWA settings. The Beams instance ID drives the browser push client.</small>
                    </div>
                </div>
            </div>

            <!-- ============ BRANDING & ASSETS ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-image text-secondary mr-1"></i> Branding &amp; Assets</h6>
                        <small class="text-muted">Logo &amp; favicon uploads</small>
                    </div>
                    <div class="card-body p-4">
                        @php
                            $logoPath       = \App\SiteSetting::assetPath('site_logo', 'images/logo.svg');
                            $logoWhitePath = \App\SiteSetting::assetPath('site_logo_white', 'images/logo-white.svg');
                            $faviconPath   = \App\SiteSetting::assetPath('site_favicon', 'images/logo-icon.svg');
                            $usingDefaultLogo      = old('site_logo', $settings['site_logo']) === 'default' || empty(old('site_logo', $settings['site_logo']));
                            $usingDefaultLogoWhite = old('site_logo_white', $settings['site_logo_white']) === 'default' || empty(old('site_logo_white', $settings['site_logo_white']));
                            $usingDefaultFavicon   = old('site_favicon', $settings['site_favicon']) === 'default' || empty(old('site_favicon', $settings['site_favicon']));
                        @endphp

                        <!-- Main Logo -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="small text-dark font-weight-bold"><i class="fa fa-picture-o text-primary mr-1"></i> Main Site Logo</strong>
                                @if($usingDefaultLogo)
                                    <span class="badge badge-secondary border">Default</span>
                                @else
                                    <span class="badge badge-success text-white">Custom Active</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center mb-2 bg-white p-2 border rounded">
                                <img src="{{ $logoPath }}" id="preview_site_logo" alt="Main Logo Preview" class="p-1" style="max-height: 42px; width: auto; object-fit: contain;">
                                <div class="ml-auto small text-muted font-italic">Current / Live Preview</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_logo_file">Upload New Logo File</label>
                                <input type="file" name="site_logo_file" id="site_logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif"
                                       class="form-control-file form-control-sm" onchange="previewBrandingImage(this, 'preview_site_logo')">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_logo_url">Or External Logo URL</label>
                                <input type="url" name="site_logo_url" id="site_logo_url" class="form-control form-control-sm"
                                       value="{{ old('site_logo_url', filter_var($settings['site_logo'], FILTER_VALIDATE_URL) ? $settings['site_logo'] : '') }}"
                                       placeholder="https://example.com/logo.png" oninput="previewBrandingUrl(this, 'preview_site_logo')">
                            </div>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="reset_site_logo" name="reset_site_logo" value="1">
                                <label class="custom-control-label small text-muted" for="reset_site_logo">Restore built-in default logo</label>
                            </div>
                        </div>

                        <!-- White / Footer Logo -->
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="small text-dark font-weight-bold"><i class="fa fa-picture-o text-info mr-1"></i> White Logo (Footer &amp; Admin Sidebar)</strong>
                                @if($usingDefaultLogoWhite)
                                    <span class="badge badge-secondary border">Default</span>
                                @else
                                    <span class="badge badge-success text-white">Custom Active</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center mb-2 bg-dark p-2 border rounded">
                                <img src="{{ $logoWhitePath }}" id="preview_site_logo_white" alt="White Logo Preview" class="p-1" style="max-height: 42px; width: auto; object-fit: contain;">
                                <div class="ml-auto small text-muted font-italic">Current / Live Preview</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_logo_white_file">Upload New White Logo File</label>
                                <input type="file" name="site_logo_white_file" id="site_logo_white_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif"
                                       class="form-control-file form-control-sm" onchange="previewBrandingImage(this, 'preview_site_logo_white')">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_logo_white_url">Or External White Logo URL</label>
                                <input type="url" name="site_logo_white_url" id="site_logo_white_url" class="form-control form-control-sm"
                                       value="{{ old('site_logo_white_url', filter_var($settings['site_logo_white'], FILTER_VALIDATE_URL) ? $settings['site_logo_white'] : '') }}"
                                       placeholder="https://example.com/logo-white.png" oninput="previewBrandingUrl(this, 'preview_site_logo_white')">
                            </div>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="reset_site_logo_white" name="reset_site_logo_white" value="1">
                                <label class="custom-control-label small text-muted" for="reset_site_logo_white">Restore built-in default white logo</label>
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="small text-dark font-weight-bold"><i class="fa fa-bookmark text-warning mr-1"></i> Favicon / Browser Icon</strong>
                                @if($usingDefaultFavicon)
                                    <span class="badge badge-secondary border">Default</span>
                                @else
                                    <span class="badge badge-success text-white">Custom Active</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center mb-2 bg-white p-2 border rounded">
                                <img src="{{ $faviconPath }}" id="preview_site_favicon" alt="Favicon Preview" class="p-1" style="max-height: 32px; width: auto; object-fit: contain;">
                                <div class="ml-auto small text-muted font-italic">Current / Live Preview</div>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_favicon_file">Upload New Favicon File</label>
                                <input type="file" name="site_favicon_file" id="site_favicon_file" accept="image/png,image/jpeg,image/webp,image/svg+xml,image/gif,image/x-icon,.ico"
                                       class="form-control-file form-control-sm" onchange="previewBrandingImage(this, 'preview_site_favicon')">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-secondary font-weight-bold mb-1" for="site_favicon_url">Or External Favicon URL</label>
                                <input type="url" name="site_favicon_url" id="site_favicon_url" class="form-control form-control-sm"
                                       value="{{ old('site_favicon_url', filter_var($settings['site_favicon'], FILTER_VALIDATE_URL) ? $settings['site_favicon'] : '') }}"
                                       placeholder="https://example.com/favicon.ico" oninput="previewBrandingUrl(this, 'preview_site_favicon')">
                            </div>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="reset_site_favicon" name="reset_site_favicon" value="1">
                                <label class="custom-control-label small text-muted" for="reset_site_favicon">Restore built-in default favicon</label>
                            </div>
                        </div>

                        <small class="text-muted d-block mt-3"><i class="fa fa-info-circle text-info mr-1"></i> Accepted file types: PNG, JPG, JPEG, WEBP, SVG, GIF (favicon also accepts .ICO). Max size: 2 MB per file. Uploads or URLs are saved permanently when clicking "Save All Settings".</small>
                    </div>
                </div>
            </div>

            <!-- ============ SUPPORT ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-envelope text-warning mr-1"></i> Contact & Support</h6>
                        <small class="text-muted">Contact page label</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="form-group mb-0">
                            <label for="contact_label" class="font-weight-bold small text-secondary">Contact Page Label</label>
                            <input type="text" name="contact_label" id="contact_label" class="form-control"
                                   value="{{ old('contact_label', $settings['contact_label']) }}">
                            <small class="text-muted">Used on the footer support link and the contact page heading.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ MAINTENANCE MODE ============ -->
            <div class="col-lg-6 col-12 mb-4">
                <div class="card border-0 shadow-sm bg-white h-100" style="border-left: 4px solid #dc3545;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="font-weight-bold text-danger mb-0"><i class="fa fa-shield text-danger mr-1"></i> Maintenance Mode</h6>
                        <small class="text-muted">Gate the public site</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="maintenance_active" name="maintenance_active"
                                   value="1" @if(old('maintenance_active', $settings['maintenance_active']) === '1') checked @endif>
                            <label class="custom-control-label font-weight-bold small" for="maintenance_active">
                                Enable maintenance mode
                            </label>
                        </div>

                        <div class="form-group mb-3">
                            <label for="maintenance_message" class="font-weight-bold small text-secondary">Message Shown to Visitors</label>
                            <textarea name="maintenance_message" id="maintenance_message" rows="2" class="form-control">{{ old('maintenance_message', $settings['maintenance_message']) }}</textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label for="maintenance_allowed_links" class="font-weight-bold small text-secondary">Allowed Links (comma-separated)</label>
                            <input type="text" name="maintenance_allowed_links" id="maintenance_allowed_links" class="form-control"
                                   value="{{ old('maintenance_allowed_links', $settings['maintenance_allowed_links']) }}"
                                   placeholder="https://twitter.com/,https://youtube.com/">
                            <small class="text-muted">Pages that stay accessible while maintenance is on (e.g. admin panel, legal).</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Save button (submits the main form) -->
            <div class="border-top pt-3 mt-2 text-right">
                <button type="submit" class="btn btn-primary font-weight-bold px-4">
                    <i class="fa fa-save mr-1"></i> Save All Settings
                </button>
            </div>
    </form>

    <!-- Separate reset form (kept outside the main form to avoid nested forms) -->
    <div class="border-top pt-3 mt-1 d-flex justify-content-start">
        <form action="{{ route('admin.settings.reset') }}" method="post"
              onsubmit="return confirm('Reset ALL site settings to default values? This cannot be undone.');">
            {{ csrf_field() }}
            <button type="submit" class="btn btn-outline-danger font-weight-bold px-3">
                <i class="fa fa-undo mr-1"></i> Reset to Defaults
            </button>
        </form>
    </div>

@endsection

@section('scripts')
<script>
    function previewBrandingImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewBrandingUrl(input, previewId) {
        var url = (input.value || '').trim();
        if (url && (url.indexOf('http://') === 0 || url.indexOf('https://') === 0)) {
            var preview = document.getElementById(previewId);
            if (preview) {
                preview.src = url;
            }
        }
    }
</script>
@endsection
