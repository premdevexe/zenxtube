<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\InstallationManager;
use App\Services\Installer\InstallationStateManager;
use Exception;
use Illuminate\Http\Request;

class InstallController extends Controller
{
    protected $installationManager;
    protected $stateManager;

    public function __construct(
        InstallationManager $installationManager,
        InstallationStateManager $stateManager
    ) {
        $this->installationManager = $installationManager;
        $this->stateManager = $stateManager;
    }

    /**
     * Display Step 5: Automatic Installation Progress View.
     */
    public function index()
    {
        // If already installed, redirect to completion
        if ($this->stateManager->isInstalled()) {
            return redirect()->route('installer.complete');
        }

        $state = $this->stateManager->getState();

        if (empty($state['database'])) {
            return redirect()->route('installer.database')
                ->with('error', 'Database settings are required before installation.');
        }

        if (empty($state['configuration'])) {
            return redirect()->route('installer.configuration')
                ->with('error', 'Application and Administrator settings are required before installation.');
        }

        $stages = $this->installationManager->getStages();
        $completedStages = $state['completed_stages'] ?? [];

        return view('installer.progress', compact('stages', 'completedStages', 'state'));
    }

    /**
     * AJAX endpoint: execute a single discrete installation stage.
     */
    public function processStage(Request $request)
    {
        $stage = $request->input('stage');

        if (empty($stage)) {
            return response()->json([
                'success' => false,
                'message' => 'Installation stage identifier is required.',
            ], 422);
        }

        $stages = $this->installationManager->getStages();
        if (!array_key_exists($stage, $stages)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid installation stage '{$stage}'.",
            ], 422);
        }

        try {
            $result = $this->installationManager->executeStage($stage);

            return response()->json(array_merge([
                'success'     => true,
                'stage'       => $stage,
                'stage_info'  => $stages[$stage],
                'progress'    => $stages[$stage]['weight'],
            ], $result));

        } catch (Exception $e) {
            return response()->json([
                'success'    => false,
                'stage'      => $stage,
                'stage_info' => $stages[$stage],
                'message'    => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display Step 6: Installation Complete Screen.
     */
    public function complete()
    {
        $state = $this->stateManager->getState();
        $isInstalled = $this->stateManager->isInstalled();

        $appUrl = $state['configuration']['app_url'] ?? url('/');
        $adminEmail = $state['configuration']['admin_email'] ?? 'admin@zenxdownload.com';
        $adminLoginUrl = rtrim($appUrl, '/') . '/admin/login';
        $databaseName = $state['database']['database'] ?? config('database.connections.mysql.database', 'zen_x_download');
        $completionTime = $state['stage_meta']['finalize']['completed_at'] ?? date('Y-m-d H:i:s');

        return view('installer.complete', compact(
            'isInstalled',
            'appUrl',
            'adminEmail',
            'adminLoginUrl',
            'databaseName',
            'completionTime'
        ));
    }

    /**
     * Display error recovery screen.
     */
    public function error()
    {
        $state = $this->stateManager->getState();
        return view('installer.error', compact('state'));
    }
}

