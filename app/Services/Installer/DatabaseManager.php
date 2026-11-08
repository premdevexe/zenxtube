<?php

namespace App\Services\Installer;

use PDO;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    /**
     * Test a database connection with provided credentials.
     *
     * @param array $credentials
     * @return array
     */
    public function testConnection(array $credentials): array
    {
        $host     = trim($credentials['host'] ?? '127.0.0.1');
        $port     = trim((string)($credentials['port'] ?? '3306'));
        $database = trim($credentials['database'] ?? '');
        $username = trim($credentials['username'] ?? '');
        $password = (string)($credentials['password'] ?? '');

        if (empty($host) || empty($database)) {
            return [
                'success' => false,
                'message' => 'Database Host and Database Name are required.',
            ];
        }

        try {
            // First attempt to connect directly to the target database
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 5,
            ];

            $pdo = new PDO($dsn, $username, $password, $options);
            $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

            // Test basic permissions by creating and dropping a probe table
            $probeTable = '_zenx_probe_' . substr(md5(uniqid('', true)), 0, 8);
            try {
                $pdo->exec("CREATE TABLE `{$probeTable}` (`id` INT NOT NULL PRIMARY KEY)");
                $pdo->exec("DROP TABLE `{$probeTable}`");
                $canCreateTable = true;
            } catch (Exception $e) {
                $canCreateTable = false;
            }

            return [
                'success'           => true,
                'message'           => 'Successfully connected to the database.',
                'server_version'    => $version,
                'database'          => $database,
                'can_create_tables' => $canCreateTable,
            ];

        } catch (Exception $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();

            // Check if error is specifically unknown database (Error 1049)
            if (strpos($errorMessage, 'Unknown database') !== false || $errorCode == 1049) {
                // Try connecting without dbname to check if user has privileges to create the DB
                try {
                    $rootDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $rootPdo = new PDO($rootDsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5,
                    ]);

                    // Attempt to create database
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    return [
                        'success'           => true,
                        'message'           => "Database '{$database}' did not exist, but was successfully created.",
                        'server_version'    => $rootPdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                        'database'          => $database,
                        'can_create_tables' => true,
                    ];
                } catch (Exception $createEx) {
                    return [
                        'success' => false,
                        'message' => "Database '{$database}' does not exist on this server, and your MySQL user does not have permission to create it automatically. Please create the database in your cPanel MySQL Databases panel first.",
                        'error_code' => 'DB_NOT_FOUND',
                    ];
                }
            }

            // Clean error message to avoid disclosing raw password if contained in DSN string
            $safeError = preg_replace('/password=[^;]*/i', 'password=***', $errorMessage);
            $safeError = preg_replace('/:[^:@]*@/', ':***@', $safeError);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $safeError,
                'error_code' => $errorCode,
            ];
        }
    }

    /**
     * Dynamically apply database configuration to runtime Laravel DB facade.
     *
     * @param array $config
     * @return void
     */
    public function applyRuntimeConfig(array $config): void
    {
        $connection = 'mysql';

        Config::set("database.connections.{$connection}.host", $config['host'] ?? '127.0.0.1');
        Config::set("database.connections.{$connection}.port", $config['port'] ?? '3306');
        Config::set("database.connections.{$connection}.database", $config['database'] ?? '');
        Config::set("database.connections.{$connection}.username", $config['username'] ?? '');
        Config::set("database.connections.{$connection}.password", $config['password'] ?? '');

        DB::purge($connection);
        DB::reconnect($connection);
    }
}

