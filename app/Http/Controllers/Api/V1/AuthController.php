<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Api\TokenAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    protected $authService;

    public function __construct(TokenAuthService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Register a client device and return initial token pair.
     * POST /api/v1/device/register
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_uuid' => 'required|string|max:100',
            'platform' => 'nullable|string|in:android,ios,web',
            'device_model' => 'nullable|string|max:100',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:30',
            'fcm_token' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', 422, $validator->errors());
        }

        $client = $this->currentClient($request);

        list($success, $result) = $this->authService->registerDevice($client, $request->all());

        if (!$success) {
            return $this->errorResponse($result, 403);
        }

        return $this->successResponse($result, 'Device successfully registered.', 201);
    }

    /**
     * Refresh access token using a rotating refresh token.
     * POST /api/v1/auth/refresh
     */
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed.', 422, $validator->errors());
        }

        list($success, $result) = $this->authService->rotateRefreshToken(
            $request->input('refresh_token'),
            $request->ip()
        );

        if (!$success) {
            return $this->errorResponse($result, 401);
        }

        return $this->successResponse($result, 'Tokens refreshed successfully.');
    }

    /**
     * Revoke all active tokens for the device (Logout).
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $device = $this->currentDevice($request);

        if (!$device) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $this->authService->revokeDevice($device);

        return $this->successResponse(null, 'Successfully logged out and revoked all device tokens.');
    }

    /**
     * Get authenticated device profile and status.
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $device = $this->currentDevice($request);

        if (!$device) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        $activeToken = $device->activeToken;

        return $this->successResponse([
            'device' => [
                'id' => $device->id,
                'device_uuid' => $device->device_uuid,
                'platform' => $device->platform,
                'device_model' => $device->device_model,
                'os_version' => $device->os_version,
                'app_version' => $device->app_version,
                'last_active_at' => $device->last_active_at ? $device->last_active_at->toIso8601String() : null,
                'registered_client' => $device->client ? $device->client->name : 'Direct API',
            ],
            'token' => $activeToken ? [
                'access_expires_at' => $activeToken->access_expires_at ? $activeToken->access_expires_at->toIso8601String() : null,
                'refresh_expires_at' => $activeToken->refresh_expires_at ? $activeToken->refresh_expires_at->toIso8601String() : null,
            ] : null,
        ], 'Device profile retrieved.');
    }
}
