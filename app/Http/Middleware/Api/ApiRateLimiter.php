<?php

namespace App\Http\Middleware\Api;

use App\ApiSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|string|null  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAttempts = null, $decayMinutes = 1)
    {
        // Determine signature
        $device = $request->attributes->get('api_device');
        $client = $request->attributes->get('api_client');

        if ($device) {
            $identifier = 'dev:' . $device->id;
            $defaultLimit = (int)ApiSetting::get('rate_limit_auth_per_min', 120);
        } elseif ($client) {
            $identifier = 'cli:' . $client->id;
            $defaultLimit = $client->rate_limit_per_minute ?: 120;
        } else {
            $identifier = 'ip:' . $request->ip();
            $defaultLimit = (int)ApiSetting::get('rate_limit_public_per_min', 60);
        }

        $limit = $maxAttempts ? (int)$maxAttempts : $defaultLimit;
        $key = 'api_rate_limit:' . md5($identifier . ':' . $request->path() . ':' . floor(time() / (60 * $decayMinutes)));

        $current = (int)Cache::get($key, 0);

        if ($current >= $limit) {
            $retryAfter = ($decayMinutes * 60) - (time() % ($decayMinutes * 60));
            return response()->json([
                'success' => false,
                'code' => 429,
                'message' => 'Too Many Requests: Rate limit exceeded. Please wait ' . $retryAfter . ' seconds.',
                'retry_after_seconds' => $retryAfter,
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'api_version' => 'v1',
                ],
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $retryAfter,
            ]);
        }

        Cache::put($key, $current + 1, $decayMinutes);
        $remaining = max(0, $limit - ($current + 1));

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }
}
