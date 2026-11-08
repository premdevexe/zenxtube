<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallationStateManager;
use Closure;

class EnsureNotInstalled
{
    /**
     * Handle an incoming request.
     * Blocks access to installer pages when application is installed/locked,
     * redirecting visitors to the normal homepage (/).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $stateManager = new InstallationStateManager();

        if ($stateManager->isInstalled()) {
            // Allow completion screen to display summary after installation
            if ($request->is('installer/complete') || $request->is('*/complete')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The application is already installed and the installer is permanently locked.',
                    'installed' => true,
                ], 403);
            }

            // Redirect to normal homepage (/)
            return redirect('/')->with('info', 'The application is already installed.');
        }

        return $next($request);
    }
}
