<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

// Force reload .env so that updated database/environment configurations take effect immediately
try {
    if (file_exists(__DIR__.'/../.env')) {
        (new Dotenv\Dotenv(__DIR__.'/../'))->overload();
    }
} catch (\Exception $e) {
    // Continue if .env is missing or cannot be read
}

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// PHP 8.1+ / 8.2 compatibility for Symfony 4 & Laravel 5.6
if (!empty($_FILES)) {
    $stripFullPath = function (&$files) use (&$stripFullPath) {
        foreach ($files as &$file) {
            if (is_array($file)) {
                unset($file['full_path']);
                if (isset($file['name']) && is_array($file['name'])) {
                    $stripFullPath($file);
                }
            }
        }
    };
    $stripFullPath($_FILES);
}

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
