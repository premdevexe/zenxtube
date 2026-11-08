<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Downloader Routes
Route::get('/', "MainController@index")->name('home');
Route::post('download', "MainController@download")->name('download');
Route::get('download', function() { return redirect('/'); });
Route::get('download/file', "MainController@downloadFile")->name('download.file');
Route::get('download/merge', "MainController@downloadMerged")->name('download.merge');

// Information & Legal Pages
Route::get('privacy-policy', "MainController@privacy")->name('privacy');
Route::get('terms-of-service', "MainController@terms")->name('terms');
Route::get('about', "MainController@about")->name('about');
Route::get('faq', "MainController@faq")->name('faq');
Route::get('dmca', "MainController@dmca")->name('dmca');
Route::get('how-to-download', "MainController@howToDownload")->name('how-to-download');
Route::get('install', "MainController@install")->name('install');
Route::get('installation', "MainController@install")->name('installation');
Route::get('contact', "MainController@contact")->name('contact');
Route::post('contact', "MainController@submitContact")->name('contact.submit');
Route::get('docs', 'AdminApiController@docs')->name('docs');
Route::get('api/docs', 'AdminApiController@docs')->name('api.docs');

// Admin Authentication Routes
Route::get('admin/login', 'AdminController@showLogin')->name('admin.login');
Route::post('admin/login', 'AdminController@login')->name('admin.login.submit');
Route::post('admin/logout', 'AdminController@logout')->name('admin.logout');

// Protected Admin Panel Routes
Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
    Route::get('/', 'AdminController@dashboard')->name('admin.dashboard');
    Route::get('dashboard', 'AdminController@dashboard');
    
    Route::get('downloads', 'AdminController@downloads')->name('admin.downloads');
    Route::post('downloads/clear', 'AdminController@clearDownloads')->name('admin.downloads.clear');
    
    Route::get('messages', 'AdminController@messages')->name('admin.messages');
    Route::post('messages/{id}/status', 'AdminController@updateMessageStatus')->name('admin.messages.status');
    Route::delete('messages/{id}', 'AdminController@deleteMessage')->name('admin.messages.delete');
    
    Route::get('system', 'AdminController@system')->name('admin.system');
    Route::post('system/clear-storage', 'AdminController@clearStorage')->name('admin.system.clear-storage');
    Route::post('system/prune-downloads', 'AdminController@pruneOldStorage')->name('admin.system.prune-storage');
    Route::post('system/clear-cache', 'AdminController@clearCache')->name('admin.system.clear-cache');
    Route::post('system/purge-dummy-data', 'AdminController@purgeDummyData')->name('admin.system.purge-dummy-data');
    
    Route::get('notifications', 'AdminController@notifications')->name('admin.notifications');
    Route::post('notifications/send', 'AdminController@sendNotification')->name('admin.notifications.send');
    Route::post('notifications/{id}/toggle', 'AdminController@toggleBanner')->name('admin.notifications.toggle');
    Route::delete('notifications/{id}', 'AdminController@deleteNotification')->name('admin.notifications.delete');

    Route::get('profile', 'AdminController@profile')->name('admin.profile');
    Route::post('profile', 'AdminController@updateProfile')->name('admin.profile.update');

    Route::get('settings', 'AdminController@settings')->name('admin.settings');
    Route::post('settings', 'AdminController@updateSettings')->name('admin.settings.update');
    Route::post('settings/reset', 'AdminController@resetSettings')->name('admin.settings.reset');

    // API Platform Management Suite
    Route::get('api', 'AdminApiController@index')->name('admin.api.index');
    Route::get('api/settings', 'AdminApiController@settings')->name('admin.api.settings');
    Route::post('api/settings', 'AdminApiController@updateSettings')->name('admin.api.settings.update');
    Route::get('api/clients', 'AdminApiController@clients')->name('admin.api.clients');
    Route::post('api/clients', 'AdminApiController@createClient')->name('admin.api.clients.create');
    Route::post('api/clients/{id}/toggle', 'AdminApiController@toggleClient')->name('admin.api.clients.toggle');
    Route::delete('api/clients/{id}', 'AdminApiController@deleteClient')->name('admin.api.clients.delete');
    Route::get('api/devices', 'AdminApiController@devices')->name('admin.api.devices');
    Route::post('api/devices/{id}/toggle-block', 'AdminApiController@toggleBlockDevice')->name('admin.api.devices.toggle_block');
    Route::delete('api/devices/{id}', 'AdminApiController@deleteDevice')->name('admin.api.devices.delete');
    Route::get('api/tokens', 'AdminApiController@tokens')->name('admin.api.tokens');
    Route::post('api/tokens/{id}/revoke', 'AdminApiController@revokeToken')->name('admin.api.tokens.revoke');
    Route::post('api/devices/{deviceId}/revoke-all-tokens', 'AdminApiController@revokeAllDeviceTokens')->name('admin.api.tokens.revoke_all');
    Route::get('api/logs', 'AdminApiController@logs')->name('admin.api.logs');
    Route::post('api/logs/clear', 'AdminApiController@clearLogs')->name('admin.api.logs.clear');
    Route::get('api/docs', 'AdminApiController@docs')->name('admin.api.docs');
});
