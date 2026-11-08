<?php

/**
 * Zen X Installer Automated Verification Suite
 * Tests RequirementChecker, DatabaseManager, EnvironmentManager,
 * InstallationStateManager, InstallationManager, and Middleware.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/', 'GET');
$app->instance('request', $request);
$kernel->bootstrap();

echo "======================================================\n";
echo "   ZEN X INSTALLER MODULE AUTOMATED TEST SUITE        \n";
echo "======================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest($description, $condition) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo " [PASS] " . $description . "\n";
        $testsPassed++;
    } else {
        echo " [FAIL] " . $description . "\n";
        $testsFailed++;
    }
}

// -------------------------------------------------------------
// 1. TEST REQUIREMENT CHECKER
// -------------------------------------------------------------
echo "\n--- [1] Testing RequirementChecker ---\n";
$reqChecker = new \App\Services\Installer\RequirementChecker();
$reqResults = $reqChecker->checkAll();

assertTest("checkAll() returns array with passes key", is_array($reqResults) && isset($reqResults['passes']));
assertTest("PHP version check succeeds on PHP 8.2+", $reqResults['php']['passed'] === true);
assertTest("PHP extensions are checked (11 extensions)", count($reqResults['extensions']['items']) >= 10);
assertTest("PDO MySQL extension is loaded", isset($reqResults['extensions']['items']['pdo_mysql']) && $reqResults['extensions']['items']['pdo_mysql']['loaded']);
assertTest("cURL extension is loaded", isset($reqResults['extensions']['items']['curl']) && $reqResults['extensions']['items']['curl']['loaded']);
assertTest("Storage permissions checked", isset($reqResults['permissions']['items']['storage/app/']));
assertTest("Environment file writability checked", isset($reqResults['environment']['passed']));

// -------------------------------------------------------------
// 2. TEST DATABASE MANAGER
// -------------------------------------------------------------
echo "\n--- [2] Testing DatabaseManager ---\n";
$dbManager = new \App\Services\Installer\DatabaseManager();

// Test invalid credentials
$invalidTest = $dbManager->testConnection([
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'non_existent_db_' . uniqid(),
    'username' => 'invalid_user_' . uniqid(),
    'password' => 'wrong_pass',
]);
assertTest("Invalid DB credentials return success = false", $invalidTest['success'] === false);
assertTest("Invalid DB credentials do not disclose raw password in error", strpos($invalidTest['message'], 'wrong_pass') === false);

// Test valid connection with current active DB
$validTest = $dbManager->testConnection([
    'host' => config('database.connections.mysql.host', '127.0.0.1'),
    'port' => config('database.connections.mysql.port', '3306'),
    'database' => config('database.connections.mysql.database', 'zen_x_download'),
    'username' => config('database.connections.mysql.username', 'root'),
    'password' => config('database.connections.mysql.password', ''),
]);
assertTest("Valid DB connection returns success = true", $validTest['success'] === true);
assertTest("Server version detected and reported", !empty($validTest['server_version']));

// -------------------------------------------------------------
// 3. TEST ENVIRONMENT MANAGER
// -------------------------------------------------------------
echo "\n--- [3] Testing EnvironmentManager ---\n";
$envManager = new \App\Services\Installer\EnvironmentManager();

assertTest(".env file exists", $envManager->exists());
$initialKey = $envManager->get('APP_KEY');
assertTest("APP_KEY exists in current .env", !empty($initialKey));

// Test backup
$backupFile = $envManager->backup();
assertTest(".env backup successfully created in storage/app/installer/backups/", !empty($backupFile) && file_exists($backupFile));

// Test key preservation
$ensuredKey = $envManager->ensureAppKey();
assertTest("ensureAppKey() preserves existing APP_KEY without overwriting", $ensuredKey === $initialKey);

// -------------------------------------------------------------
// 4. TEST INSTALLATION STATE MANAGER
// -------------------------------------------------------------
echo "\n--- [4] Testing InstallationStateManager ---\n";
$stateManager = new \App\Services\Installer\InstallationStateManager();
$lockPath = $stateManager->getLockFile();

// Ensure clean test start
$stateManager->clearLock();

assertTest("isInstalled() is initially false when lock file is absent", $stateManager->isInstalled() === false);

// Save state
$stateManager->saveState([
    'test_key' => 'zenx_test_value',
    'database' => ['host' => '127.0.0.1', 'database' => 'zen_x_download'],
]);
$state = $stateManager->getState();
assertTest("saveState() persists to storage/app/installer/installation.json", isset($state['test_key']) && $state['test_key'] === 'zenx_test_value');

// Test stage completion marking
$stateManager->markStageComplete('backup_env', ['test' => true]);
assertTest("isStageComplete('backup_env') returns true after marking", $stateManager->isStageComplete('backup_env') === true);
assertTest("isStageComplete('finalize') returns false before marking", $stateManager->isStageComplete('finalize') === false);

// -------------------------------------------------------------
// 5. TEST ADMIN ACCOUNT MANAGER
// -------------------------------------------------------------
echo "\n--- [5] Testing AdminAccountManager ---\n";
$adminManager = new \App\Services\Installer\AdminAccountManager();
$userModel = $adminManager->resolveUserModel();
assertTest("resolveUserModel() finds active User model", class_exists($userModel));

// -------------------------------------------------------------
// 6. TEST INSTALLATION MANAGER PIPELINE
// -------------------------------------------------------------
echo "\n--- [6] Testing InstallationManager Pipeline ---\n";
$installManager = new \App\Services\Installer\InstallationManager(
    $reqChecker,
    $dbManager,
    $envManager,
    new \App\Services\Installer\MigrationManager(),
    $adminManager,
    $stateManager
);

$stages = $installManager->getStages();
assertTest("getStages() defines all 8 required pipeline stages", count($stages) === 8);
assertTest("backup_env stage is defined", isset($stages['backup_env']));
assertTest("write_config stage is defined", isset($stages['write_config']));
assertTest("test_db stage is defined", isset($stages['test_db']));
assertTest("generate_key stage is defined", isset($stages['generate_key']));
assertTest("migrate stage is defined", isset($stages['migrate']));
assertTest("seed stage is defined", isset($stages['seed']));
assertTest("create_admin stage is defined", isset($stages['create_admin']));
assertTest("finalize stage is defined", isset($stages['finalize']));

// Test discrete stage execution
$backupStageResult = $installManager->executeStage('backup_env');
assertTest("executeStage('backup_env') executes successfully", isset($backupStageResult['success']) && $backupStageResult['success'] === true);
assertTest("backup_env marked completed in state manager", $stateManager->isStageComplete('backup_env') === true);

$keyStageResult = $installManager->executeStage('generate_key');
assertTest("executeStage('generate_key') executes successfully", isset($keyStageResult['success']) && $keyStageResult['success'] === true);
assertTest("generate_key marked completed in state manager", $stateManager->isStageComplete('generate_key') === true);

// Clean up test state before next section
$stateManager->clearState();

// -------------------------------------------------------------
// 7. TEST ENSURE NOT INSTALLED MIDDLEWARE
// -------------------------------------------------------------
echo "\n--- [7] Testing EnsureNotInstalled Middleware ---\n";
$middleware = new \App\Http\Middleware\EnsureNotInstalled();

// When NOT locked:
$request = \Illuminate\Http\Request::create('/installer', 'GET');
$passedThrough = false;
$response = $middleware->handle($request, function() use (&$passedThrough) {
    $passedThrough = true;
    return response('OK');
});
assertTest("Middleware permits access to /installer when unlocked", $passedThrough === true);

// Create lock
$lockCreated = $stateManager->createLock(['test' => true]);
assertTest("createLock() creates installed.lock file", file_exists($lockCreated));
assertTest("isInstalled() returns true after lock file created", $stateManager->isInstalled() === true);

// Test detection of base_path('install.lock')
assertTest("Codebase install.lock is recognized", file_exists(base_path('install.lock')));

// When locked:
$passedThrough = false;
$response = $middleware->handle($request, function() use (&$passedThrough) {
    $passedThrough = true;
    return response('OK');
});
assertTest("Middleware blocks access to /installer when locked", $passedThrough === false);
assertTest("Middleware redirects to normal page (/) when locked", $response->isRedirection() && strpos($response->headers->get('Location'), '/') !== false);

// AJAX request when locked:
$ajaxRequest = \Illuminate\Http\Request::create('/installer/install/process', 'POST');
$ajaxRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
$ajaxResponse = $middleware->handle($ajaxRequest, function() {});
assertTest("Middleware returns HTTP 403 JSON for AJAX request when locked", $ajaxResponse->getStatusCode() === 403);

// Exemption: /installer/complete allowed when locked
$completeRequest = \Illuminate\Http\Request::create('/installer/complete', 'GET');
$completePassed = false;
$completeResponse = $middleware->handle($completeRequest, function() use (&$completePassed) {
    $completePassed = true;
    return response('OK');
});
assertTest("Middleware allows access to /installer/complete even when locked", $completePassed === true);

// Clean up lock files after testing so user/host can choose initial state
$stateManager->clearLock();
$stateManager->clearState();
echo "\nTest cleanup: Removed temporary test lock and transient state.\n";

// -------------------------------------------------------------
// 8. TEST HTTP CONTROLLER ROUTE DISPATCHING
// -------------------------------------------------------------
echo "\n--- [8] Testing HTTP Route Dispatching via Kernel ---\n";

function dispatchRoute($kernel, $method, $uri, $params = [], $headers = []) {
    $req = \Illuminate\Http\Request::create($uri, $method, $params);
    
    // Start session and attach CSRF token for web routes
    $session = app('session')->driver();
    $session->setId('test_session_' . md5($uri));
    $session->start();
    $req->setLaravelSession($session);
    $req->headers->set('X-CSRF-TOKEN', $session->token());
    
    foreach ($headers as $k => $v) {
        $req->headers->set($k, $v);
    }
    return $kernel->handle($req);
}

// 1. GET /install redirects to /installer
$respInstall = dispatchRoute($kernel, 'GET', '/install');
assertTest("GET /install redirects to /installer", $respInstall->isRedirection() && strpos($respInstall->headers->get('Location'), '/installer') !== false);

// 2. GET /installer renders Step 1 Welcome
$respWelcome = dispatchRoute($kernel, 'GET', '/installer');
assertTest("GET /installer returns HTTP 200", $respWelcome->getStatusCode() === 200);
assertTest("GET /installer contains Welcome title", strpos($respWelcome->getContent(), 'Installation Setup') !== false);

// 3. GET /installer/requirements renders Step 2
$respReq = dispatchRoute($kernel, 'GET', '/installer/requirements');
assertTest("GET /installer/requirements returns HTTP 200", $respReq->getStatusCode() === 200);
assertTest("GET /installer/requirements contains Server Requirements", strpos($respReq->getContent(), 'Server prerequisites') !== false);

// 4. GET /installer/requirements/check returns JSON
$respCheck = dispatchRoute($kernel, 'GET', '/installer/requirements/check', [], ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
assertTest("GET /installer/requirements/check returns HTTP 200 JSON", $respCheck->getStatusCode() === 200);
$jsonCheck = json_decode($respCheck->getContent(), true);
assertTest("AJAX check returns passes flag", isset($jsonCheck['passes']));

// 5. GET /installer/database renders Step 3
$respDb = dispatchRoute($kernel, 'GET', '/installer/database');
assertTest("GET /installer/database returns HTTP 200", $respDb->getStatusCode() === 200);
assertTest("GET /installer/database contains Database Connection", strpos($respDb->getContent(), 'Database connection') !== false);

// 6. POST /installer/database/test AJAX test connection
$testPayload = [
    'host' => config('database.connections.mysql.host', '127.0.0.1'),
    'port' => config('database.connections.mysql.port', '3306'),
    'database' => config('database.connections.mysql.database', 'zen_x_download'),
    'username' => config('database.connections.mysql.username', 'root'),
    'password' => config('database.connections.mysql.password', ''),
];
$respDbTest = dispatchRoute($kernel, 'POST', '/installer/database/test', $testPayload, ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
assertTest("POST /installer/database/test returns HTTP 200", $respDbTest->getStatusCode() === 200);
$jsonDbTest = json_decode($respDbTest->getContent(), true);
assertTest("POST /installer/database/test reports valid connection", isset($jsonDbTest['success']) && $jsonDbTest['success'] === true);

// 7. GET /installer/complete renders Step 6
$respComplete = dispatchRoute($kernel, 'GET', '/installer/complete');
assertTest("GET /installer/complete returns HTTP 200", $respComplete->getStatusCode() === 200);
assertTest("GET /installer/complete contains celebration header", strpos($respComplete->getContent(), 'Installation verified') !== false);

// 8. Verify root slug redirects to /installer when uninstalled
$respHome = dispatchRoute($kernel, 'GET', '/');
assertTest("GET / slug automatically redirects to /installer when uninstalled", $respHome->isRedirection() && strpos($respHome->headers->get('Location'), '/installer') !== false);

// -------------------------------------------------------------
// 9. TEST ARTISAN COMMANDS (reset:install, installer:status, installer:lock)
// -------------------------------------------------------------
echo "\n--- [9] Testing Artisan Core Commands ---\n";

// Test installer:lock
$exitLock = \Illuminate\Support\Facades\Artisan::call('installer:lock', ['--force' => true]);
assertTest("php artisan installer:lock returns exit code 0", $exitLock === 0);
assertTest("installer:lock creates installed.lock", $stateManager->isInstalled() === true);

// When locked, verify / renders normally and /installer is blocked
$respHomeLocked = dispatchRoute($kernel, 'GET', '/');
assertTest("GET / (Homepage) returns HTTP 200 when locked", $respHomeLocked->getStatusCode() === 200);

$respInstallerLocked = dispatchRoute($kernel, 'GET', '/installer');
assertTest("GET /installer redirects to normal page (/) when locked", $respInstallerLocked->isRedirection() && strpos($respInstallerLocked->headers->get('Location'), '/') !== false);

$respInstallLocked = dispatchRoute($kernel, 'GET', '/install');
assertTest("GET /install redirects to normal page (/) when locked", $respInstallLocked->isRedirection() && strpos($respInstallLocked->headers->get('Location'), '/') !== false);

// Test installer:status
$exitStatus = \Illuminate\Support\Facades\Artisan::call('installer:status');
assertTest("php artisan installer:status returns exit code 0", $exitStatus === 0);
$outputStatus = \Illuminate\Support\Facades\Artisan::output();
assertTest("installer:status reports LOCKED state", strpos($outputStatus, 'LOCKED') !== false);

// Test reset:install
$exitReset = \Illuminate\Support\Facades\Artisan::call('reset:install', ['--force' => true]);
assertTest("php artisan reset:install returns exit code 0", $exitReset === 0);
assertTest("reset:install removes all lock files", $stateManager->isInstalled() === false);

// After reset, verify / redirects back to /installer
$respHomeAfterReset = dispatchRoute($kernel, 'GET', '/');
assertTest("GET / slug redirects to /installer after reset:install", $respHomeAfterReset->isRedirection() && strpos($respHomeAfterReset->headers->get('Location'), '/installer') !== false);

// Clean up
$stateManager->clearLock();
$stateManager->clearState();

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n======================================================\n";
echo "   TEST RESULTS: {$testsPassed} PASSED, {$testsFailed} FAILED\n";
echo "======================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
