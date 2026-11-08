<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\InstallationStateManager;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    protected $stateManager;

    public function __construct(InstallationStateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * Display Step 1: Welcome Screen.
     */
    public function index(Request $request)
    {
        $laravelVersion = app()->version();
        $phpVersion = PHP_VERSION;
        $appName = config('installer.name', 'Zen X Download');
        $state = $this->stateManager->getState();

        return view('installer.welcome', compact('laravelVersion', 'phpVersion', 'appName', 'state'));
    }
}

