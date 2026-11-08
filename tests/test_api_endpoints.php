<?php

require __DIR__ . '/../vendor/autoload.php';

function testEndpoint($method, $path, $body = null, $headers = []) {
    $url = 'http://127.0.0.1:8000/api/v1' . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $defaultHeaders = ['Accept: application/json'];
    if ($body !== null) {
        $defaultHeaders[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['code' => $httpCode, 'data' => json_decode($response, true), 'raw' => $response];
}

echo "=== TESTING API V1 SUITE ===\n\n";

// 1. Status Check
$res = testEndpoint('GET', '/status');
echo "[1] GET /status => HTTP {$res['code']} | Status: " . ($res['data']['data']['status'] ?? 'FAIL') . "\n";

// 2. Config Check
$res = testEndpoint('GET', '/config');
echo "[2] GET /config => HTTP {$res['code']} | App: " . ($res['data']['data']['app_name'] ?? 'FAIL') . "\n";

// 3. Register Device
$regData = [
    'device_uuid' => 'test-device-uuid-' . time(),
    'platform' => 'android',
    'device_model' => 'Samsung Galaxy S24 Ultra',
    'os_version' => 'Android 14',
    'app_version' => '1.0.0',
];
$res = testEndpoint('POST', '/device/register', $regData, ['X-Client-ID: zenx-android-v1']);
echo "[3] POST /device/register => HTTP {$res['code']} | Message: " . ($res['data']['message'] ?? 'FAIL') . "\n";

$accessToken = $res['data']['data']['auth']['access_token'] ?? null;
$refreshToken = $res['data']['data']['auth']['refresh_token'] ?? null;
echo "    Access Token: " . substr($accessToken, 0, 18) . "...\n";
echo "    Refresh Token: " . substr($refreshToken, 0, 18) . "...\n";

// 4. Authenticated /auth/me
$res = testEndpoint('GET', '/auth/me', null, ["Authorization: Bearer {$accessToken}"]);
echo "[4] GET /auth/me => HTTP {$res['code']} | Model: " . ($res['data']['data']['device']['device_model'] ?? 'FAIL') . "\n";

// 5. Rotate Refresh Token
$res = testEndpoint('POST', '/auth/refresh', ['refresh_token' => $refreshToken]);
echo "[5] POST /auth/refresh => HTTP {$res['code']} | New Token: " . (isset($res['data']['data']['auth']['access_token']) ? 'OK' : 'FAIL') . "\n";
$newAccessToken = $res['data']['data']['auth']['access_token'] ?? $accessToken;

// 6. Test SSRF Protection on /video/info
$badUrls = [
    'http://127.0.0.1:8000/admin',
    'http://localhost/private',
    'http://169.254.169.254/latest/meta-data',
    'http://192.168.1.1/router',
    'ftp://example.com/file.mp4'
];

foreach ($badUrls as $badUrl) {
    $res = testEndpoint('POST', '/video/info', ['url' => $badUrl]);
    echo "[6.SSRF] URL: {$badUrl} => HTTP {$res['code']} | " . ($res['data']['message'] ?? 'OK') . "\n";
}

echo "\nAPI SUITE PREVIEW COMPLETE.\n";

// 7. Verify Web Home Page Regression
$ch = curl_init('http://127.0.0.1:8000/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$web = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "[7] GET / (Home Page) => HTTP {$httpCode} | " . (strpos($web, 'Zen X Download') !== false ? 'OK (Intact)' : 'FAIL') . "\n";
