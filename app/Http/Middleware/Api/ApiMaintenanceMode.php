<?php

namespace App\Http\Middleware\Api;

use App\ApiSetting;
use Closure;
use Illuminate\Http\Request;

class ApiMaintenanceMode
{
    /**
     * Handle an incoming request and check if maintenance mode is enabled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isMaintenance = ApiSetting::get('api_maintenance_mode', false);

        if ($isMaintenance) {
            $message = ApiSetting::get('api_maintenance_message', 'Zen X API platform is currently undergoing scheduled maintenance.');
            return response()->json([
                'success' => false,
                'code' => 503,
                'message' => $message,
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'api_version' => 'v1',
                    'maintenance' => true,
                ],
            ], 503);
        }

        return $next($request);
    }
}
