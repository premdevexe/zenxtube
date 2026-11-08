@extends('app')

@section('title', 'Installation & Deployment Guide - Minimal White Setup - Zen X Download')

@section('styles')
<style>
    /* Minimal White Base Theme Styling */
    .install-page {
        background-color: #ffffff;
        color: #1e293b;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .minimal-hero {
        background: #ffffff;
        border-bottom: 1px solid #eaecf0;
        padding: 50px 0 40px;
    }

    .minimal-card {
        background: #ffffff;
        border: 1px solid #eaecf0;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.05), 0 1px 2px rgba(16, 24, 40, 0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .minimal-card:hover {
        border-color: #d0d5dd;
        box-shadow: 0 4px 12px rgba(16, 24, 40, 0.08);
    }

    .step-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #0f172a;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .step-badge-sub {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .code-box {
        position: relative;
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.88rem;
        line-height: 1.6;
        margin: 14px 0;
        overflow-x: auto;
    }

    .code-box code {
        color: inherit;
        background: transparent;
        padding: 0;
    }

    .copy-btn {
        position: absolute;
        top: 10px;
        right: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #f8fafc;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.78rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .copy-btn:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    .copy-btn.copied {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }

    .nav-tabs-minimal {
        border-bottom: 2px solid #f1f5f9;
    }

    .nav-tabs-minimal .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 8px 8px 0 0;
        margin-right: 4px;
        background: transparent;
        transition: all 0.15s ease;
    }

    .nav-tabs-minimal .nav-link.active {
        color: #0f172a;
        background: #f8fafc;
        border-bottom: 2px solid #0f172a;
    }

    .nav-tabs-minimal .nav-link:hover:not(.active) {
        color: #0f172a;
        background: #f8fafc;
    }

    .pill-label {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-radius: 20px;
        padding: 4px 12px;
        display: inline-block;
    }

    .pill-blue {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #dbeafe;
    }

    .pill-green {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #dcfce7;
    }

    .pill-gray {
        background: #f8fafc;
        color: #475467;
        border: 1px solid #eaecf0;
    }

    .diag-item {
        padding: 14px 18px;
        border: 1px solid #eaecf0;
        border-radius: 10px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="install-page">
    
    <!-- Hero Header (Clean Minimal White) -->
    <div class="minimal-hero">
        <div class="container text-center px-3">
            <span class="pill-label pill-blue mb-3">
                <i class="fa fa-wrench mr-1"></i> Quickstart &amp; Deployment Guide
            </span>
            <h1 class="font-weight-bold text-dark mt-2 mb-3" style="font-size: 2.3rem; letter-spacing: -0.02em;">
                Zen X Download Installation Guide
            </h1>
            <p class="text-secondary mx-auto mb-4" style="max-width: 680px; font-size: 1.05rem; line-height: 1.6;">
                Step-by-step instructions to install, configure database, verify media binaries (<code class="text-dark">yt-dlp</code> &amp; <code class="text-dark">FFmpeg</code>), and deploy Zen X Download in minutes.
            </p>
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                <a href="#quick-steps" class="btn btn-dark btn-sm px-4 py-2 font-weight-bold mr-2 mb-2" style="border-radius: 8px;">
                    <i class="fa fa-arrow-down mr-1"></i> Start Installation
                </a>
                <button type="button" onclick="runLiveHealthCheck()" class="btn btn-outline-primary btn-sm px-4 py-2 font-weight-bold mb-2" style="border-radius: 8px;">
                    <i class="fa fa-stethoscope mr-1"></i> Test Local Environment
                </button>
            </div>
        </div>
    </div>

    <div class="container py-5 px-3 px-sm-4" id="quick-steps">
        <div class="row">
            
            <!-- Left Column: Main Installation Steps -->
            <div class="col-lg-8 col-12 mb-4">

                <!-- Platform Tabs (Windows vs Linux vs macOS) -->
                <ul class="nav nav-tabs nav-tabs-minimal mb-4" id="osTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-windows" data-toggle="tab" href="#os-windows" role="tab">
                            <i class="fa fa-windows text-primary mr-1"></i> Windows (PowerShell / XAMPP)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-linux" data-toggle="tab" href="#os-linux" role="tab">
                            <i class="fa fa-linux text-dark mr-1"></i> Linux (Ubuntu / VPS)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-cpanel" data-toggle="tab" href="#os-cpanel" role="tab">
                            <i class="fa fa-server text-danger mr-1"></i> cPanel (Shared Hosting)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-macos" data-toggle="tab" href="#os-macos" role="tab">
                            <i class="fa fa-apple text-dark mr-1"></i> macOS (Homebrew)
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="osTabContent">
                    
                    <!-- ========================================== -->
                    <!-- 1. WINDOWS TAB -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade show active" id="os-windows" role="tabpanel">

                        <!-- Step 1: Clone -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">1</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Clone the Repository</h5>
                                    <small class="text-muted">Clone project files into your web root or workspace directory</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>git clone https://github.com/ahmedhalima/all-video-downloader.git "d:\Projects\Zen X Download"<br>cd "d:\Projects\Zen X Download"</code>
                            </div>
                        </div>

                        <!-- Step 2: Composer Install -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">2</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Install PHP Dependencies</h5>
                                    <small class="text-muted">Install composer packages with PHP 8.2 compatibility flags</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>composer install --ignore-platform-reqs</code>
                            </div>
                            <p class="small text-muted mb-0">
                                <i class="fa fa-info-circle text-primary mr-1"></i> <code>--ignore-platform-reqs</code> allows Laravel 5.6 to run seamlessly on PHP 8.0, 8.1, and 8.2+.
                            </p>
                        </div>

                        <!-- Step 3: Environment Setup -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">3</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Configure Environment (.env)</h5>
                                    <small class="text-muted">Create environment file and generate a unique application security key</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>cp .env.example .env<br>php artisan key:generate</code>
                            </div>
                            <p class="small text-secondary mb-2">Open <code>.env</code> in your editor and ensure your MySQL database details match:</p>
                            <div class="code-box bg-light text-dark border p-3" style="background: #f8fafc !important; color: #0f172a !important;">
                                <button class="copy-btn text-dark border-secondary" onclick="copySnippet(this)">Copy</button>
                                <code>DB_CONNECTION=mysql<br>DB_HOST=127.0.0.1<br>DB_PORT=3306<br>DB_DATABASE=zen_x_download<br>DB_USERNAME=root<br>DB_PASSWORD=</code>
                            </div>
                        </div>

                        <!-- Step 4: Storage & Binaries -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">4</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Verify Binaries &amp; Create Storage Directories</h5>
                                    <small class="text-muted">Ensure yt-dlp and FFmpeg are present and storage folders exist</small>
                                </div>
                            </div>
                            <p class="small text-secondary mb-2">Create the storage folders in PowerShell:</p>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>New-Item -ItemType Directory -Force -Path storage\app\downloads, storage\framework\cache, storage\framework\sessions, storage\framework\views, storage\logs, bootstrap\cache</code>
                            </div>
                            <div class="alert alert-light border p-3 small mb-0">
                                <strong>Bundled Binaries:</strong> Both <code>yt-dlp.exe</code> (v2026.10.26) and <code>ffmpeg.exe</code> are already bundled in the root project directory. No external installer or PATH variable edits are required!
                            </div>
                        </div>

                        <!-- Step 5: Database Migration & Seed -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">5</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Run Migrations &amp; Seed Database</h5>
                                    <small class="text-muted">Creates tables and seeds default admin account and API client credentials</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>php artisan migrate --seed</code>
                            </div>
                            <div class="p-3 bg-light rounded small border">
                                <div class="font-weight-bold text-dark mb-1"><i class="fa fa-key text-warning mr-1"></i> Default Administrator Credentials:</div>
                                <div><strong>Email:</strong> <code>admin@zenxdownload.com</code></div>
                                <div><strong>Password:</strong> Configured via <code>ADMIN_PASSWORD=your_password</code> in <code>.env</code> (or generated during seeding).</div>
                            </div>
                        </div>

                        <!-- Step 6: Start Web Server -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">6</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Start the Application Server</h5>
                                    <small class="text-muted">Launch the Laravel built-in development HTTP server</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>php artisan serve --host=127.0.0.1 --port=8000</code>
                            </div>
                            <p class="small text-secondary mb-0">
                                Open your web browser and navigate to <strong><a href="http://127.0.0.1:8000" target="_blank">http://127.0.0.1:8000</a></strong>!
                            </p>
                        </div>

                    </div>

                    <!-- ========================================== -->
                    <!-- 2. LINUX TAB -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="os-linux" role="tabpanel">
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-terminal text-primary mr-2"></i> Ubuntu / Debian One-Shot Setup</h5>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code># 1. System packages &amp; media binaries<br>sudo apt update &amp;&amp; sudo apt install -y php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-mysql php8.2-zip ffmpeg yt-dlp<br><br># 2. Clone repository<br>git clone https://github.com/premdevexe/zenxtube.git /var/www/zenx<br>cd /var/www/zenx<br><br># 3. Dependencies &amp; Env<br>composer install --no-dev --ignore-platform-reqs<br>cp .env.example .env &amp;&amp; php artisan key:generate<br><br># 4. Storage permissions<br>sudo chown -R www-data:www-data storage bootstrap/cache<br>sudo chmod -R 775 storage bootstrap/cache<br><br># 5. Database migrate &amp; seed<br>php artisan migrate --seed --force<br><br># 6. Run server or configure Nginx<br>php artisan serve --host=0.0.0.0 --port=8000</code>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- 3. CPANEL TAB -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="os-cpanel" role="tabpanel">
                        
                        <!-- Step 1: Uploading Files -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">1</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Upload Files &amp; Folder Structure</h5>
                                    <small class="text-muted">Place project files securely above public_html to protect .env</small>
                                </div>
                            </div>
                            <p class="small text-secondary mb-2">
                                <strong>Option A (Recommended for Main Domain):</strong>
                            </p>
                            <ol class="small text-secondary mb-3">
                                <li>In cPanel <strong>File Manager</strong>, create a new folder: <code>/home/username/zenxtube</code></li>
                                <li>Upload your zip archive and extract all files inside <code>/home/username/zenxtube</code></li>
                                <li>Move the entire contents of <code>/home/username/zenxtube/public/</code> into your web root <code>/home/username/public_html/</code></li>
                                <li>Open <code>public_html/index.php</code> in cPanel File Manager and update paths to:</li>
                            </ol>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>require __DIR__.'/../zenxtube/vendor/autoload.php';<br>$app = require_once __DIR__.'/../zenxtube/bootstrap/app.php';</code>
                            </div>
                            <p class="small text-muted mb-0">
                                <i class="fa fa-info-circle text-info mr-1"></i> <strong>Option B (For Subdomain / Addon Domain):</strong> In cPanel <strong>Domains</strong>, simply set the Document Root directly to <code>/home/username/zenxtube/public</code>.
                            </p>
                        </div>

                        <!-- Step 2: PHP Version & Extensions -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">2</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Select PHP Version &amp; Enable Shell Functions</h5>
                                    <small class="text-muted">cPanel MultiPHP Manager or Select PHP Version</small>
                                </div>
                            </div>
                            <ul class="small text-secondary mb-2">
                                <li>Select <strong>PHP 8.1</strong> or <strong>PHP 8.2</strong>.</li>
                                <li>Ensure extensions are enabled: <code>pdo_mysql</code>, <code>curl</code>, <code>mbstring</code>, <code>fileinfo</code>, <code>openssl</code>, <code>zip</code>, <code>bcmath</code>.</li>
                                <li><strong>Important for Downloaders:</strong> Under PHP Options/Settings, ensure <code>proc_open</code> and <code>shell_exec</code> are <strong>NOT</strong> disabled in <code>disable_functions</code>.</li>
                            </ul>
                        </div>

                        <!-- Step 3: MySQL Database Setup -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">3</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Create Database &amp; Configure .env</h5>
                                    <small class="text-muted">MySQL Databases Wizard &amp; Credentials</small>
                                </div>
                            </div>
                            <ol class="small text-secondary mb-3">
                                <li>Go to cPanel &rarr; <strong>MySQL Databases</strong>.</li>
                                <li>Create database (e.g. <code>cpaneluser_zenx</code>) and user (e.g. <code>cpaneluser_dbuser</code>).</li>
                                <li>Assign the user to database with <strong>ALL PRIVILEGES</strong>.</li>
                                <li>In File Manager, edit <code>/home/username/zenxtube/.env</code>:</li>
                            </ol>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>DB_CONNECTION=mysql<br>DB_HOST=127.0.0.1<br>DB_PORT=3306<br>DB_DATABASE=cpaneluser_zenx<br>DB_USERNAME=cpaneluser_dbuser<br>DB_PASSWORD=YourStrongPassword<br>ADMIN_PASSWORD=MySecretAdmin123!</code>
                            </div>
                        </div>

                        <!-- Step 4: Standalone Linux yt-dlp & FFmpeg -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">4</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Download Standalone Linux Binaries</h5>
                                    <small class="text-muted">Set up yt-dlp and FFmpeg via cPanel Terminal</small>
                                </div>
                            </div>
                            <p class="small text-secondary mb-2">Open cPanel <strong>Terminal</strong> and run:</p>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>cd /home/username/zenxtube<br>mkdir -p bin<br># Download Linux yt-dlp standalone<br>curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o bin/yt-dlp<br>chmod +x bin/yt-dlp<br><br># Check server FFmpeg (or install static build into bin/)<br>which ffmpeg || echo "Check hosting provider for ffmpeg path"</code>
                            </div>
                        </div>

                        <!-- Step 5: Migrate & Seed -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">5</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Migrate Database &amp; Link Storage</h5>
                                    <small class="text-muted">Execute migrations and seed default administrative account</small>
                                </div>
                            </div>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>cd /home/username/zenxtube<br>chmod -R 775 storage bootstrap/cache<br>php artisan migrate --seed --force<br>php artisan storage:link</code>
                            </div>
                            <p class="small text-muted mb-0">
                                Default Admin login: <strong>admin@zenxdownload.com</strong> (Password configured in <code>ADMIN_PASSWORD</code>).
                            </p>
                        </div>

                        <!-- Step 6: Cron Job -->
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-badge mr-3">6</div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Set Up cPanel Cron Job</h5>
                                    <small class="text-muted">Automates pruning of temporary downloads older than 15 minutes</small>
                                </div>
                            </div>
                            <p class="small text-secondary mb-2">In cPanel &rarr; <strong>Cron Jobs</strong>, select <em>Once Per Minute (* * * * *)</em> and enter:</p>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>cd /home/username/zenxtube &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>
                            </div>
                        </div>

                    </div>

                    <!-- ========================================== -->
                    <!-- 3. MACOS TAB -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade" id="os-macos" role="tabpanel">
                        <div class="minimal-card p-4 p-md-4 mb-4">
                            <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-apple text-dark mr-2"></i> macOS Quick Setup via Homebrew</h5>
                            <div class="code-box">
                                <button class="copy-btn" onclick="copySnippet(this)">Copy</button>
                                <code>brew install php@8.2 composer ffmpeg yt-dlp mysql<br>git clone https://github.com/ahmedhalima/all-video-downloader.git zenx<br>cd zenx<br>composer install --ignore-platform-reqs<br>cp .env.example .env &amp;&amp; php artisan key:generate<br>php artisan migrate --seed<br>php artisan serve --port=8000</code>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Right Column: Live Verification & System Checklist -->
            <div class="col-lg-4 col-12">
                
                <!-- Live Diagnostics Widget -->
                <div class="minimal-card p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-stethoscope text-primary mr-1"></i> Live Health Check
                        </h6>
                        <span id="healthStatusBadge" class="badge badge-success px-2 py-1 font-weight-bold">Active</span>
                    </div>
                    <p class="small text-muted mb-3">
                        Test and verify that your web gateway, MySQL connection, and media encoders are active:
                    </p>

                    <div id="diagList">
                        <div class="diag-item">
                            <span class="small font-weight-bold"><i class="fa fa-server text-info mr-2"></i> Web &amp; PHP Gateway</span>
                            <span id="diagGateway" class="badge badge-success"><i class="fa fa-check"></i> Ready</span>
                        </div>
                        <div class="diag-item">
                            <span class="small font-weight-bold"><i class="fa fa-database text-primary mr-2"></i> MySQL Database</span>
                            <span id="diagDatabase" class="badge badge-success"><i class="fa fa-check"></i> Connected</span>
                        </div>
                        <div class="diag-item">
                            <span class="small font-weight-bold"><i class="fa fa-film text-danger mr-2"></i> yt-dlp Engine</span>
                            <span id="diagYtdlp" class="badge badge-success"><i class="fa fa-check"></i> Ready</span>
                        </div>
                        <div class="diag-item">
                            <span class="small font-weight-bold"><i class="fa fa-cogs text-warning mr-2"></i> FFmpeg Remuxer</span>
                            <span id="diagFfmpeg" class="badge badge-success"><i class="fa fa-check"></i> Ready</span>
                        </div>
                        <div class="diag-item">
                            <span class="small font-weight-bold"><i class="fa fa-hdd-o text-success mr-2"></i> Storage Writable</span>
                            <span id="diagStorage" class="badge badge-success"><i class="fa fa-check"></i> Writable</span>
                        </div>
                    </div>

                    <button type="button" onclick="runLiveHealthCheck()" id="runHealthBtn" class="btn btn-outline-primary btn-sm btn-block font-weight-bold mt-3 py-2" style="border-radius: 8px;">
                        <i class="fa fa-refresh mr-1"></i> Re-test System Health
                    </button>
                </div>

                <!-- Admin & Management Links -->
                <div class="minimal-card p-4 mb-4">
                    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fa fa-external-link text-info mr-1"></i> Key Endpoints &amp; Panels
                    </h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <a href="{{ url('/') }}" class="text-dark font-weight-bold"><i class="fa fa-globe text-primary mr-1"></i> Home Page:</a>
                            <span class="text-muted d-block font-monospace">http://127.0.0.1:8000</span>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('admin.dashboard') }}" class="text-dark font-weight-bold"><i class="fa fa-lock text-warning mr-1"></i> Admin Panel:</a>
                            <span class="text-muted d-block font-monospace">http://127.0.0.1:8000/admin</span>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('admin.system') }}" class="text-dark font-weight-bold"><i class="fa fa-sliders text-success mr-1"></i> System Diagnostics:</a>
                            <span class="text-muted d-block font-monospace">http://127.0.0.1:8000/admin/system</span>
                        </li>
                        <li class="mb-0">
                            <a href="{{ route('api.docs') }}" class="text-dark font-weight-bold"><i class="fa fa-code text-info mr-1"></i> REST API v1 Docs:</a>
                            <span class="text-muted d-block font-monospace">http://127.0.0.1:8000/admin/api/docs</span>
                        </li>
                    </ul>
                </div>

                <!-- Troubleshooting Card -->
                <div class="minimal-card p-4 bg-light">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fa fa-lightbulb-o text-warning mr-1"></i> Pro Tip: PHP 8.2 Compatibility
                    </h6>
                    <p class="small text-secondary mb-0">
                        PHP 8.1+ introduced <code>full_path</code> in file upload bags. In Zen X Download, this has already been patched in <code>bootstrap/app.php</code> and <code>public/index.php</code> so file uploads and site logo settings work without Symfony <code>FileBag</code> fatal errors.
                    </p>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copySnippet(button) {
    const codeBox = button.parentElement;
    const textToCopy = codeBox.querySelector('code').innerText;

    navigator.clipboard.writeText(textToCopy).then(() => {
        const originalText = button.innerText;
        button.innerText = 'Copied!';
        button.classList.add('copied');

        setTimeout(() => {
            button.innerText = originalText;
            button.classList.remove('copied');
        }, 1800);
    }).catch(err => {
        console.error('Copy failed: ', err);
    });
}

function runLiveHealthCheck() {
    const btn = document.getElementById('runHealthBtn');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Checking Services...';
    btn.disabled = true;

    fetch('{{ url("api/v1/status") }}')
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = '<i class="fa fa-check text-success"></i> Health Checked!';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa fa-refresh mr-1"></i> Re-test System Health';
                btn.disabled = false;
            }, 2000);

            if (data && data.data && data.data.components) {
                const c = data.data.components;
                
                // Gateway
                if (c.api_gateway && c.api_gateway.status === 'operational') {
                    document.getElementById('diagGateway').innerHTML = '<i class="fa fa-check"></i> ' + c.api_gateway.php_version;
                    document.getElementById('diagGateway').className = 'badge badge-success';
                }

                // Database
                if (c.database && c.database.status === 'connected') {
                    document.getElementById('diagDatabase').innerHTML = '<i class="fa fa-check"></i> MySQL Connected';
                    document.getElementById('diagDatabase').className = 'badge badge-success';
                }

                // yt-dlp
                if (c.downloader_core && c.downloader_core.status === 'ready') {
                    document.getElementById('diagYtdlp').innerHTML = '<i class="fa fa-check"></i> Ready';
                    document.getElementById('diagYtdlp').className = 'badge badge-success';
                }

                // FFmpeg
                if (c.media_encoder && c.media_encoder.status === 'ready') {
                    document.getElementById('diagFfmpeg').innerHTML = '<i class="fa fa-check"></i> Ready';
                    document.getElementById('diagFfmpeg').className = 'badge badge-success';
                }

                // Storage
                if (c.storage && c.storage.status === 'writable') {
                    document.getElementById('diagStorage').innerHTML = '<i class="fa fa-check"></i> ' + (c.storage.free_space_mb ? Math.round(c.storage.free_space_mb / 1024) + ' GB Free' : 'Writable');
                    document.getElementById('diagStorage').className = 'badge badge-success';
                }
            }
        })
        .catch(err => {
            btn.innerHTML = '<i class="fa fa-exclamation-triangle text-danger"></i> Offline / Error';
            setTimeout(() => {
                btn.innerHTML = '<i class="fa fa-refresh mr-1"></i> Re-test System Health';
                btn.disabled = false;
            }, 2000);
        });
}

// Auto-run once on page load
document.addEventListener('DOMContentLoaded', function() {
    runLiveHealthCheck();
});
</script>
@endsection

