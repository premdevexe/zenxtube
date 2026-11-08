<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallationStateManager;
use Closure;

class EnsureInstalled
{
    /**
     * Handle an incoming request.
     * When application is NOT installed/locked (e.g. after reset:install),
     * automatically redirects the root "/" slug and other application routes
     * directly to the installation wizard (/installer).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $stateManager = new InstallationStateManager();

        // If not installed, redirect to installer (excluding installer routes & assets)
        if (!$stateManager->isInstalled()) {
            $path = trim($request->path(), '/');

            // Allow installer routes, install guides, health checks, and static asset paths
            if (!preg_match('#^(installer|install-guide|installation-guide|install|installation|css|js|images|fonts|uploads|favicon\.ico|api/v1/status)#i', $path)) {
                return redirect()->route('installer.welcome');
            }
        }

        return $next($request);
    }
}

