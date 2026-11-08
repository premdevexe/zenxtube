<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Zen X Download — Enterprise REST API Platform v1
|--------------------------------------------------------------------------
|
| Centralized REST API for Web Frontend, Android Mobile App, and future iOS App.
| Middleware stack includes strict SSRF protection, telemetry logging, sliding-window
| rate limiting, maintenance gatekeeping, and device-bound bearer token authentication.
|
*/

Route::prefix('v1')->group(function () {

    // ==========================================
    // 1. PUBLIC PLATFORM & CONFIGURATION (No Auth Required)
    // ==========================================
    Route::group(['middleware' => ['api.maintenance', 'api.logger', 'api.rate_limit:60,1']], function () {
        Route::get('/status', 'Api\V1\ConfigApiController@status')->name('api.v1.status');
        Route::get('/config', 'Api\V1\ConfigApiController@config')->name('api.v1.config');
        Route::get('/app/version', 'Api\V1\ConfigApiController@version')->name('api.v1.app.version');
    });

    // ==========================================
    // 2. DEVICE REGISTRATION & TOKEN ROTATION
    // ==========================================
    Route::group(['middleware' => ['api.maintenance', 'api.logger', 'api.client:optional', 'api.rate_limit:30,1']], function () {
        Route::post('/device/register', 'Api\V1\AuthController@registerDevice')->name('api.v1.device.register');
        Route::post('/auth/refresh', 'Api\V1\AuthController@refresh')->name('api.v1.auth.refresh');
    });

    // ==========================================
    // 3. AUTHENTICATED DEVICE MANAGEMENT
    // ==========================================
    Route::group(['middleware' => ['api.maintenance', 'api.logger', 'api.auth', 'api.rate_limit:60,1']], function () {
        Route::get('/auth/me', 'Api\V1\AuthController@me')->name('api.v1.auth.me');
        Route::post('/auth/logout', 'Api\V1\AuthController@logout')->name('api.v1.auth.logout');
        Route::get('/download/history', 'Api\V1\DownloadApiController@history')->name('api.v1.download.history');
    });

    // ==========================================
    // 4. MEDIA EXTRACTION & METADATA
    // ==========================================
    Route::group(['middleware' => ['api.maintenance', 'api.logger', 'api.rate_limit:30,1']], function () {
        Route::post('/video/info', 'Api\V1\VideoApiController@info')->name('api.v1.video.info');
        Route::post('/playlist/info', 'Api\V1\VideoApiController@playlist')->name('api.v1.playlist.info');
    });

    // ==========================================
    // 5. MEDIA DOWNLOADS & SECURE STREAMING
    // ==========================================
    Route::group(['middleware' => ['api.maintenance', 'api.logger']], function () {
        // Create download job (stricter rate limit: 15/min)
        Route::post('/download', 'Api\V1\DownloadApiController@download')
            ->middleware('api.rate_limit:15,1')
            ->name('api.v1.download.create');

        // Check job status & formats
        Route::get('/download/{id}/status', 'Api\V1\DownloadApiController@status')->name('api.v1.download.status');
        Route::get('/download/{id}/formats', 'Api\V1\DownloadApiController@formats')->name('api.v1.download.formats');

        // Secure file streaming (Range requests / Resume supported)
        Route::get('/download/{id}/file', 'Api\V1\DownloadApiController@streamFile')->name('api.v1.download.file');
    });

});
