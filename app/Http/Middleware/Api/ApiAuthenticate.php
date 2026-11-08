<?php

namespace App\Http\Middleware\Api;

use App\Services\Api\TokenAuthService;
use Closure;
use Illuminate\Http\Request;

class ApiAuthenticate
{
    protected $authService;

    public function __construct(TokenAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (empty($header) && $request->has('access_token')) {
            $header = 'Bearer ' . $request->input('access_token');
        }

        if (empty($header)) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Unauthenticated: Bearer access token is required.',
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'api_version' => 'v1',
                ],
            ], 401);
        }

        $device = $this->authService->validateAccessToken($header);

        if (!$device) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Unauthenticated: Token is invalid, expired, or device has been blocked.',
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'api_version' => 'v1',
                ],
            ], 401);
        }

        // Attach device to request
        $request->attributes->set('api_device', $device);

        return $next($request);
    }
}
