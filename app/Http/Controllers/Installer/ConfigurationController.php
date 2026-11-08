<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallationStateManager;
use DateTimeZone;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    protected $environmentManager;
    protected $stateManager;

    public function __construct(
        EnvironmentManager $environmentManager,
        InstallationStateManager $stateManager
    ) {
        $this->environmentManager = $environmentManager;
        $this->stateManager = $stateManager;
    }

    /**
     * Display Step 4: Application & Admin Configuration.
     */
    public function index()
    {
        $state = $this->stateManager->getState();

        // Ensure database configuration is complete
        if (empty($state['database'])) {
            return redirect()->route('installer.database')
                ->with('error', 'Please configure your database credentials before setting up application parameters.');
        }

        $timezones = DateTimeZone::listIdentifiers();
        $currentTimezone = config('app.timezone', 'UTC');

        $currentUrl = url('/');
        if (strpos($currentUrl, '127.0.0.1') === false && strpos($currentUrl, 'localhost') === false) {
            // Remove port if standard 80 or 443
            $currentUrl = preg_replace('/:(80|443)$/', '', $currentUrl);
        }

        $defaults = [
            'app_name'       => config('app.name', 'Zen X Download'),
            'app_url'        => $currentUrl,
            'timezone'       => $currentTimezone,
            'app_env'        => 'production',
            'admin_name'     => 'System Administrator',
            'admin_email'    => 'admin@' . (request()->getHost() ?: 'zenxdownload.com'),
        ];

        $current = array_merge($defaults, $state['configuration'] ?? []);

        return view('installer.configuration', compact('current', 'timezones'));
    }

    /**
     * Save application and administrator configuration to installer state.
     */
    public function save(Request $request)
    {
        $timezones = DateTimeZone::listIdentifiers();

        $request->validate([
            'app_name'       => 'required|string|max:100',
            'app_url'        => 'required|url|max:255',
            'timezone'       => 'required|string|in:' . implode(',', $timezones),
            'app_env'        => 'required|string|in:production,local',
            'admin_name'     => 'required|string|max:100',
            'admin_email'    => 'required|email|max:150',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $config = [
            'app_name'       => $request->input('app_name'),
            'app_url'        => rtrim($request->input('app_url'), '/'),
            'timezone'       => $request->input('timezone'),
            'app_env'        => $request->input('app_env'),
            'admin_name'     => $request->input('admin_name'),
            'admin_email'    => strtolower($request->input('admin_email')),
            'admin_password' => $request->input('admin_password'),
        ];

        $this->stateManager->saveState([
            'configuration' => $config,
        ]);

        return redirect()->route('installer.install');
    }
}

