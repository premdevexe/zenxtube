<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$publicFile = __DIR__.'/public'.$uri;

// If the file exists in the public directory, serve it directly
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    if (realpath($_SERVER['DOCUMENT_ROOT'] ?? '') === realpath(__DIR__.'/public')) {
        return false;
    }

    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'json'  => 'application/json',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'mp4'   => 'video/mp4',
        'mp3'   => 'audio/mpeg',
    ];

    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mime = $mimes[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($publicFile) : 'application/octet-stream');

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($publicFile));
    readfile($publicFile);
    exit;
}

require_once __DIR__.'/public/index.php';
