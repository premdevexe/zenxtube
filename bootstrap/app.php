<?php

// PHP 8.1+ / 8.2 compatibility for Symfony 4 & Laravel 5.6:
// PHP 8.1 added 'full_path' to $_FILES. Symfony 4's FileBag expects exactly 5 keys
// ('error', 'name', 'size', 'tmp_name', 'type'). If 'full_path' is present, Symfony treats
// files as nested string arrays, causing UploadedFile::createFromBase fatal type errors.
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

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
