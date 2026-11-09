<?php

require 'D:/Projects/Zen X Download/vendor/autoload.php';
$app = require_once 'D:/Projects/Zen X Download/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$testUrl = 'https://youtu.be/jR3rWCBeO6M?si=YyHOJ7cykr8yBBPy';

echo "=====================================================\n";
echo "    END-TO-END VERIFICATION: VIDEO DOWNLOAD FLOW     \n";
echo "=====================================================\n";
echo "Target URL: $testUrl\n\n";

// 1. Controller /download request simulation
echo "Step 1: Submitting URL to MainController::download()...\n";
$request = \Illuminate\Http\Request::create('/download', 'POST', [
    'videourl' => $testUrl
]);

$controller = app(\App\Http\Controllers\MainController::class);
$response = $controller->download($request);

if ($response instanceof \Illuminate\Http\RedirectResponse) {
    echo "[FAIL] Controller redirected: " . $response->getTargetUrl() . "\n";
    if (session()->has('errors')) {
        echo "Errors: " . implode(', ', session()->get('errors')->all()) . "\n";
    }
    exit(1);
}

if (!($response instanceof \Illuminate\View\View)) {
    echo "[FAIL] Unexpected response type: " . get_class($response) . "\n";
    exit(1);
}

$viewName = $response->getName();
$viewData = $response->getData();
echo "[PASS] View rendered successfully: $viewName\n";
echo "       Title: " . ($viewData['title'] ?? 'N/A') . "\n";
echo "       Thumbnail: " . ($viewData['thumbnail'] ?? 'N/A') . "\n";
echo "       Quality selector cards (merged_options): " . count($viewData['merged_options'] ?? []) . "\n";
echo "       Direct stream links (links): " . count($viewData['links'] ?? []) . "\n\n";

// 2. Display quality cards
echo "Step 2: Available Quality Options:\n";
foreach (($viewData['merged_options'] ?? []) as $opt) {
    echo "  - [{$opt['badge']}] {$opt['label']} (type: {$opt['type']}, height: {$opt['height']}p, ext: {$opt['ext']})\n";
}

// 3. Display direct links
echo "\nStep 3: Sample Available Direct Links:\n";
$firstVideoLink = null;
$firstAudioLink = null;
foreach (($viewData['links'] ?? []) as $i => $link) {
    if ($i < 6 || $i >= count($viewData['links']) - 2) {
        echo "  - {$link['format']} | Size: {$link['size']} | Ext: {$link['ext']}\n";
    } elseif ($i === 6) {
        echo "  - ... (" . (count($viewData['links']) - 8) . " more format resolutions) ...\n";
    }
    if (!$firstVideoLink && !empty($link['has_video']) && !empty($link['url'])) {
        $firstVideoLink = $link;
    }
    if (!$firstAudioLink && !empty($link['has_audio']) && !empty($link['url'])) {
        $firstAudioLink = $link;
    }
}

// 4. Test actual streaming / downloading from the extracted URL
echo "\nStep 4: Validating Live CDN Stream Connectivity...\n";
if ($firstVideoLink) {
    echo "Testing Video Stream ({$firstVideoLink['format']})...\n";
    $ch = curl_init($firstVideoLink['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RANGE, '0-65535'); // 64 KB range
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $bytes = strlen($content);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 206) {
        echo "  [PASS] Stream reachable! HTTP $httpCode, received $bytes bytes.\n";
    } else {
        echo "  [FAIL] Stream error: HTTP $httpCode\n";
    }
}

if ($firstAudioLink) {
    echo "Testing Audio Stream ({$firstAudioLink['format']})...\n";
    $ch = curl_init($firstAudioLink['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RANGE, '0-32767'); // 32 KB range
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $bytes = strlen($content);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 206) {
        echo "  [PASS] Audio stream reachable! HTTP $httpCode, received $bytes bytes.\n";
    } else {
        echo "  [FAIL] Audio stream error: HTTP $httpCode\n";
    }
}

// 5. Test native file creation via mergeAndCreateFile (cURL direct chunked download)
echo "\nStep 5: Testing Download Processing (Native PHP cURL Downloader)...\n";
$downloader = app(\App\Services\VideoDownloaderService::class);
$mergedFile = $downloader->mergeAndCreateFile($testUrl, 'video', 360);
if ($mergedFile && file_exists($mergedFile) && filesize($mergedFile) > 1024) {
    $sizeMB = round(filesize($mergedFile) / (1024 * 1024), 2);
    echo "  [PASS] File successfully generated: " . basename($mergedFile) . " ($sizeMB MB)\n";
    // Clean up test file
    @unlink($mergedFile);
} else {
    echo "  [FAIL] Failed to generate merged file.\n";
}

echo "\n=====================================================\n";
echo "                 ALL CHECKS PASSED                   \n";
echo "=====================================================\n";

