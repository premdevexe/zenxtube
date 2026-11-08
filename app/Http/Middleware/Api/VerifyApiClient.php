<?php

namespace App\Http\Middleware\Api;

use App\ApiClient;
use Closure;
use Illuminate\Http\Request;

class VerifyApiClient
{
    /**
     * Handle an incoming request and authenticate the API Client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $mode 'optional' or 'required'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $mode = 'optional')
    {
        $clientId = $request->header('X-Client-ID') ?: $request->input('client_id');
        $clientSecret = $request->header('X-Client-Secret') ?: $request->input('client_secret');

        if ($clientId) {
            $client = ApiClient::where('client_id', $clientId)->where('is_active', true)->first();

            if ($client) {
                // If client_secret is provided or required, verify hash
                if ($clientSecret && !$client->verifySecret($clientSecret)) {
                    return response()->json([
                        'success' => false,
                        'code' => 401,
                        'message' => 'Invalid client credentials.',
                        'meta' => [
                            'timestamp' => now()->toIso8601String(),
                            'api_version' => 'v1',
                        ],
                    ], 401);
                }

                $request->attributes->set('api_client', $client);
                return $next($request);
            }

            if ($mode === 'required') {
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'Unknown or inactive API client.',
                    'meta' => [
                        'timestamp' => now()->toIso8601String(),
                        'api_version' => 'v1',
                    ],
                ], 401);
            }
        } elseif ($mode === 'required') {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Header X-Client-ID is required for this endpoint.',
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'api_version' => 'v1',
                ],
            ], 401);
        }

        return $next($request);
    }
}
