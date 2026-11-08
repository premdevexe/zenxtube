<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Installer Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Zen X Download Installation Wizard.
    | Designed for Laravel 5.6, 10, 11, and 12 on PHP 8.2+ with cPanel
    | shared hosting compatibility.
    |
    */

    'name' => env('APP_NAME', 'Zen X Download'),
    'version' => '2.5.0',

    /*
    |--------------------------------------------------------------------------
    | Storage and Lock File Paths
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'lock_file' => storage_path('app/installer/installed.lock'),
        'state_file' => storage_path('app/installer/installation.json'),
        'backup_dir' => storage_path('app/installer/backups'),
        'log_dir'    => storage_path('app/installer/logs'),
        'env_file'   => base_path('.env'),
        'env_example'=> base_path('.env.example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Server and Runtime Requirements
    |--------------------------------------------------------------------------
    */
    'core' => [
        'min_php_version' => '8.2.0',
    ],

    'requirements' => [
        'php' => [
            'openssl'   => 'Required for secure hashing, tokens, and SSL operations.',
            'pdo'       => 'Required for database abstraction layer.',
            'pdo_mysql' => 'Required for MySQL and MariaDB database connectivity.',
            'mbstring'  => 'Required for multibyte UTF-8 string manipulation.',
            'tokenizer' => 'Required by Laravel internal compiler and Blade engine.',
            'xml'       => 'Required for parsing XML formats and syndication feeds.',
            'ctype'     => 'Required for character type checking.',
            'json'      => 'Required for JSON serialization and API communications.',
            'bcmath'    => 'Required for high-precision arbitrary arithmetic.',
            'fileinfo'  => 'Required for reliable MIME type detection of downloads.',
            'curl'      => 'Required for fetching remote media metadata and streams.',
        ],
        'apache' => [
            'mod_rewrite',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Directory Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'storage/app/'           => '0775',
        'storage/app/downloads/' => '0775',
        'storage/framework/'     => '0775',
        'storage/framework/cache'=> '0775',
        'storage/framework/sessions' => '0775',
        'storage/framework/views'    => '0775',
        'storage/logs/'          => '0775',
        'bootstrap/cache/'       => '0775',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Defaults
    |--------------------------------------------------------------------------
    */
    'database' => [
        'default_connection' => 'mysql',
        'default_host'       => '127.0.0.1',
        'default_port'       => '3306',
        'supported_drivers'  => ['mysql', 'mariadb'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Timezones
    |--------------------------------------------------------------------------
    */
    'default_timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Seeders to execute during automated installation
    |--------------------------------------------------------------------------
    */
    'seeders' => [
        \ApiEcosystemSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Post-installation Production Flags
    |--------------------------------------------------------------------------
    */
    'production' => [
        'APP_ENV'   => 'production',
        'APP_DEBUG' => 'false',
    ],
];

