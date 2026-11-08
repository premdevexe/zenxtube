<?php

namespace App\Services\Api;

use App\ApiClient;
use App\ApiDevice;
use App\ApiToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TokenAuthService
{
    const ACCESS_TOKEN_TTL_MINUTES = 60; // 1 hour access token
    const REFRESH_TOKEN_TTL_DAYS = 30;   // 30 days refresh token

    /**
     * Register or update a device and issue a new token pair.
     *
     * @param ApiClient|null $client
     * @param array $deviceData
     * @return array [bool $success, array|string $data]
     */
    public function registerDevice($client, array $deviceData)
    {
        $uuid = isset($deviceData['device_uuid']) ? trim($deviceData['device_uuid']) : null;
        if (empty($uuid)) {
            return [false, 'Device UUID is required.'];
        }

        // Find or create device
        $device = ApiDevice::firstOrNew(['device_uuid' => $uuid]);

        if ($device->is_blocked) {
            return [false, 'Device has been blocked: ' . ($device->block_reason ?: 'Administrative policy.')];
        }

        if ($client) {
            $device->client_id = $client->id;
        }

        $device->platform = isset($deviceData['platform']) ? $deviceData['platform'] : ($device->platform ?: 'android');
        $device->device_model = isset($deviceData['device_model']) ? $deviceData['device_model'] : $device->device_model;
        $device->os_version = isset($deviceData['os_version']) ? $deviceData['os_version'] : $device->os_version;
        $device->app_version = isset($deviceData['app_version']) ? $deviceData['app_version'] : $device->app_version;
        $device->fcm_token = isset($deviceData['fcm_token']) ? $deviceData['fcm_token'] : $device->fcm_token;
        $device->ip_address = isset($deviceData['ip_address']) ? $deviceData['ip_address'] : request()->ip();
        $device->last_active_at = Carbon::now();
        $device->save();

        // Issue token pair
        $tokens = $this->issueTokens($device, request()->ip());

        return [
            true,
            [
                'device' => [
                    'device_uuid' => $device->device_uuid,
                    'platform' => $device->platform,
                    'device_model' => $device->device_model,
                    'app_version' => $device->app_version,
                ],
                'auth' => $tokens,
            ]
        ];
    }

    /**
     * Issue a new access & refresh token pair for a device.
     *
     * @param ApiDevice $device
     * @param string|null $ip
     * @return array
     */
    public function issueTokens(ApiDevice $device, $ip = null)
    {
        $plainAccessToken = 'zenx_at_' . Str::random(40) . bin2hex(random_bytes(12));
        $plainRefreshToken = 'zenx_rt_' . Str::random(40) . bin2hex(random_bytes(12));

        $accessExpiresAt = Carbon::now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES);
        $refreshExpiresAt = Carbon::now()->addDays(self::REFRESH_TOKEN_TTL_DAYS);

        $token = ApiToken::create([
            'device_id' => $device->id,
            'access_token_hash' => hash('sha256', $plainAccessToken),
            'refresh_token_hash' => hash('sha256', $plainRefreshToken),
            'access_expires_at' => $accessExpiresAt,
            'refresh_expires_at' => $refreshExpiresAt,
            'last_used_ip' => $ip ?: request()->ip(),
        ]);

        return [
            'token_type' => 'Bearer',
            'access_token' => $plainAccessToken,
            'refresh_token' => $plainRefreshToken,
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
            'access_expires_at' => $accessExpiresAt->toIso8601String(),
            'refresh_expires_at' => $refreshExpiresAt->toIso8601String(),
        ];
    }

    protected static function selfREFRESH_TOKEN_TTL_DAYS()
    {
        return self::REFRESH_TOKEN_TTL_DAYS;
    }

    /**
     * Validate an incoming Bearer access token.
     *
     * @param string $bearerToken
     * @return ApiDevice|null
     */
    public function validateAccessToken($bearerToken)
    {
        if (empty($bearerToken)) {
            return null;
        }

        if (Str::startsWith($bearerToken, 'Bearer ')) {
            $bearerToken = substr($bearerToken, 7);
        }

        $hash = hash('sha256', trim($bearerToken));

        $tokenRecord = ApiToken::with('device')
            ->where('access_token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('access_expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord || !$tokenRecord->device) {
            return null;
        }

        $device = $tokenRecord->device;

        if ($device->is_blocked) {
            return null;
        }

        // Update telemetry
        $tokenRecord->update(['last_used_ip' => request()->ip()]);
        $device->update(['last_active_at' => Carbon::now()]);

        return $device;
    }

    /**
     * Rotate tokens using a valid refresh token.
     *
     * @param string $refreshToken
     * @param string|null $ip
     * @return array [bool $success, array|string $data]
     */
    public function rotateRefreshToken($refreshToken, $ip = null)
    {
        if (empty($refreshToken)) {
            return [false, 'Refresh token is required.'];
        }

        $hash = hash('sha256', trim($refreshToken));

        $tokenRecord = ApiToken::with('device')
            ->where('refresh_token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('refresh_expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord || !$tokenRecord->device) {
            return [false, 'Invalid or expired refresh token.'];
        }

        $device = $tokenRecord->device;

        if ($device->is_blocked) {
            return [false, 'Device has been blocked: ' . ($device->block_reason ?: 'Administrative policy.')];
        }

        // Revoke the old token (rotation enforcement)
        $tokenRecord->revoke();

        // Issue new token pair
        $newTokens = $this->issueTokens($device, $ip ?: request()->ip());

        return [
            true,
            [
                'device' => [
                    'device_uuid' => $device->device_uuid,
                    'platform' => $device->platform,
                ],
                'auth' => $newTokens,
            ]
        ];
    }

    /**
     * Revoke all active tokens for a device (Logout).
     *
     * @param ApiDevice $device
     * @return bool
     */
    public function revokeDevice(ApiDevice $device)
    {
        return ApiToken::where('device_id', $device->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => Carbon::now()]);
    }

    /**
     * Generate a signed download token for streaming access.
     *
     * @param string $jobUuid
     * @param int $ttlMinutes
     * @return array [string $token, Carbon $expiresAt]
     */
    public static function createDownloadSignature($jobUuid, $ttlMinutes = 30)
    {
        $secret = config('app.key');
        if (empty($secret)) {
            // Never fall back to a public default: an empty APP_KEY must not
            // produce forge-able download tokens.
            throw new \RuntimeException('APP_KEY is not configured; cannot sign download token.');
        }
        $expiresAt = Carbon::now()->addMinutes($ttlMinutes);
        $timestamp = $expiresAt->timestamp;
        $signature = hash_hmac('sha256', $jobUuid . '|' . $timestamp, $secret);
        $fullToken = $timestamp . '.' . $signature;

        return [$fullToken, $expiresAt];
    }

    /**
     * Verify a signed download token.
     *
     * @param string $jobUuid
     * @param string $token
     * @return bool
     */
    public static function verifyDownloadSignature($jobUuid, $token)
    {
        if (empty($token) || strpos($token, '.') === false) {
            return false;
        }

        list($timestamp, $providedSignature) = explode('.', $token, 2);

        if ((int)$timestamp < time()) {
            return false; // Expired
        }

        $secret = config('app.key');
        if (empty($secret)) {
            return false; // No key configured: refuse to verify rather than fall back
        }
        $expectedSignature = hash_hmac('sha256', $jobUuid . '|' . $timestamp, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
