<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\InstallationStateManager;
use App\Services\Installer\RequirementChecker;
use Illuminate\Http\Request;

class RequirementsController extends Controller
{
    protected $requirementChecker;
    protected $stateManager;

    public function __construct(
        RequirementChecker $requirementChecker,
        InstallationStateManager $stateManager
    ) {
        $this->requirementChecker = $requirementChecker;
        $this->stateManager = $stateManager;
    }

    /**
     * Display Step 2: Server Requirements.
     */
    public function index()
    {
        $requirements = $this->requirementChecker->checkAll();

        $this->stateManager->saveState([
            'requirements_passed' => $requirements['passes'],
        ]);

        return view('installer.requirements', compact('requirements'));
    }

    /**
     * AJAX endpoint to re-check requirements dynamically.
     */
    public function check(Request $request)
    {
        $requirements = $this->requirementChecker->checkAll();

        $this->stateManager->saveState([
            'requirements_passed' => $requirements['passes'],
        ]);

        return response()->json([
            'success'      => true,
            'passes'       => $requirements['passes'],
            'requirements' => $requirements,
        ]);
    }
}

