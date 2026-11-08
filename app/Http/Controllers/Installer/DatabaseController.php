<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\DatabaseManager;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallationStateManager;
use App\Services\Installer\RequirementChecker;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    protected $databaseManager;
    protected $environmentManager;
    protected $stateManager;
    protected $requirementChecker;

    public function __construct(
        DatabaseManager $databaseManager,
        EnvironmentManager $environmentManager,
        InstallationStateManager $stateManager,
        RequirementChecker $requirementChecker
    ) {
        $this->databaseManager = $databaseManager;
        $this->environmentManager = $environmentManager;
        $this->stateManager = $stateManager;
        $this->requirementChecker = $requirementChecker;
    }

    /**
     * Display Step 3: Database Configuration.
     */
    public function index()
    {
        $state = $this->stateManager->getState();

        // Ensure requirements passed
        if (empty($state['requirements_passed'])) {
            $req = $this->requirementChecker->checkAll();
            if (!$req['passes']) {
                return redirect()->route('installer.requirements')
                    ->with('error', 'Please resolve all required server dependencies before configuring the database.');
            }
        }

        $envDb = [
            'host'     => $this->environmentManager->get('DB_HOST', '127.0.0.1'),
            'port'     => $this->environmentManager->get('DB_PORT', '3306'),
            'database' => $this->environmentManager->get('DB_DATABASE', ''),
            'username' => $this->environmentManager->get('DB_USERNAME', 'root'),
            'password' => $this->environmentManager->get('DB_PASSWORD', ''),
        ];

        $current = $state['database'] ?? $envDb;

        return view('installer.database', compact('current'));
    }

    /**
     * AJAX endpoint to test database connectivity.
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|numeric',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $credentials = [
            'host'     => $request->input('host'),
            'port'     => $request->input('port'),
            'database' => $request->input('database'),
            'username' => $request->input('username'),
            'password' => $request->input('password', ''),
        ];

        $result = $this->databaseManager->testConnection($credentials);

        return response()->json($result);
    }

    /**
     * Validate and save database configuration to installer state.
     */
    public function save(Request $request)
    {
        $request->validate([
            'host'     => 'required|string|max:255',
            'port'     => 'required|numeric|between:1,65535',
            'database' => 'required|string|max:64',
            'username' => 'required|string|max:128',
            'password' => 'nullable|string|max:255',
        ]);

        $credentials = [
            'host'     => $request->input('host'),
            'port'     => $request->input('port'),
            'database' => $request->input('database'),
            'username' => $request->input('username'),
            'password' => $request->input('password', ''),
        ];

        // Perform server-side validation of connectivity
        $test = $this->databaseManager->testConnection($credentials);
        if (!$test['success']) {
            return back()->withInput()->withErrors([
                'database_error' => $test['message'] ?? 'Could not connect to the database with provided credentials.',
            ]);
        }

        $this->stateManager->saveState([
            'database' => $credentials,
        ]);

        return redirect()->route('installer.configuration')
            ->with('success', 'Database connection verified and saved.');
    }
}

