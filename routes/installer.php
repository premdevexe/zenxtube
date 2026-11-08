<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'installer',
    'middleware' => ['web', 'installer.not_installed'],
    'namespace' => 'Installer',
], function () {
    Route::get('/', 'WelcomeController@index')->name('installer.welcome');
    Route::get('requirements', 'RequirementsController@index')->name('installer.requirements');
    Route::get('requirements/check', 'RequirementsController@check')->name('installer.requirements.check');
    Route::get('database', 'DatabaseController@index')->name('installer.database');
    Route::post('database/test', 'DatabaseController@testConnection')->name('installer.database.test');
    Route::post('database/save', 'DatabaseController@save')->name('installer.database.save');
    Route::get('configuration', 'ConfigurationController@index')->name('installer.configuration');
    Route::post('configuration/save', 'ConfigurationController@save')->name('installer.configuration.save');
    Route::get('install', 'InstallController@index')->name('installer.install');
    Route::post('install/process', 'InstallController@processStage')->name('installer.install.process');
    Route::get('complete', 'InstallController@complete')->name('installer.complete');
    Route::get('error', 'InstallController@error')->name('installer.error');
});

