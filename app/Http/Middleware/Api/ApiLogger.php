<?php

namespace App\Http\Middleware\Api;

use App\ApiLog;
use Closure;
use Illuminate\Http\Request;

class ApiLogger
{
    /**
     * Handle an incoming request and record telemetry in api_logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = (int)round((microtime(true) - $startTime) * 1000);

        try {
            $device = $request->attributes->get('api_device');
            $deviceId = $device ? $device->id : null;

            // Sanitize payload (remove secrets/passwords)
            $payload = $request->except(['password', 'client_secret', 'refresh_token', 'access_token']);
            $payloadJson = !empty($payload) ? json_encode($payload, JSON_UNESCAPED_SLASHES) : null;

            // Summarize response if JSON
            $content = $response->getContent();
            $summary = null;
            if ($content && strlen($content) < 4000) {
                $summary = $content;
            } else {
                $summary = json_encode(['bytes' => strlen($content), 'type' => $response->headers->get('Content-Type')]);
            }

            ApiLog::create([
                'device_id' => $deviceId,
                'endpoint' => substr($request->path(), 0, 191),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => substr($request->userAgent() ?: 'unknown', 0, 255),
                'request_payload' => $payloadJson,
                'response_summary' => $summary,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently to never break the API response on logging error
        }

        return $response;
    }
}
