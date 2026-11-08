<?php

namespace App\Http\Controllers;

use App\ApiClient;
use App\ApiDevice;
use App\ApiDownloadJob;
use App\ApiLog;
use App\ApiSetting;
use App\ApiToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminApiController extends Controller
{
    /**
     * API Platform Overview & Telemetry Dashboard.
     */
    public function index()
    {
        $today = Carbon::today();

        // High-level KPI metrics
        $metrics = [
            'total_requests' => ApiLog::count(),
            'requests_today' => ApiLog::where('created_at', '>=', $today)->count(),
            'total_devices' => ApiDevice::count(),
            'active_devices_24h' => ApiDevice::where('last_active_at', '>=', Carbon::now()->subDay())->count(),
            'active_tokens' => ApiToken::whereNull('revoked_at')->where('access_expires_at', '>', Carbon::now())->count(),
            'total_jobs' => ApiDownloadJob::count(),
            'completed_jobs' => ApiDownloadJob::where('status', 'completed')->count(),
            'active_clients' => ApiClient::where('is_active', true)->count(),
            'avg_latency_ms' => round((float)ApiLog::avg('duration_ms'), 1),
            'error_rate_pct' => 0,
        ];

        if ($metrics['total_requests'] > 0) {
            $errorCount = ApiLog::where('status_code', '>=', 400)->count();
            $metrics['error_rate_pct'] = round(($errorCount / $metrics['total_requests']) * 100, 2);
        }

        // Platform breakdown
        $platformBreakdown = ApiDevice::select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();

        // Recent API Requests
        $recentLogs = ApiLog::with('device')->latest('id')->take(10)->get();

        // Recent Download Jobs
        $recentJobs = ApiDownloadJob::with('device')->latest('id')->take(10)->get();

        // Maintenance status
        $isMaintenance = (bool)ApiSetting::get('api_maintenance_mode', false);
        $baseUrl = ApiSetting::get('api_base_url', url('/api/v1'));

        return view('admin.api.index', compact(
            'metrics',
            'platformBreakdown',
            'recentLogs',
            'recentJobs',
            'isMaintenance',
            'baseUrl'
        ));
    }

    /**
     * API Settings & Dynamic Config Editor.
     */
    public function settings()
    {
        $settings = ApiSetting::all()->keyBy('key');

        return view('admin.api.settings', compact('settings'));
    }

    /**
     * Update API Settings.
     */
    public function updateSettings(Request $request)
    {
        $data = $request->except(['_token']);

        // Explicit boolean handling
        $booleans = ['api_maintenance_mode', 'android_force_update', 'ios_force_update'];
        foreach ($booleans as $b) {
            $val = isset($data[$b]) && ($data[$b] == '1' || $data[$b] == 'on') ? '1' : '0';
            ApiSetting::updateOrCreate(
                ['key' => $b],
                ['value' => $val, 'type' => 'boolean', 'is_public' => true]
            );
            unset($data[$b]);
        }

        foreach ($data as $key => $val) {
            $setting = ApiSetting::where('key', $key)->first();
            if ($setting) {
                $setting->value = $val;
                $setting->save();
            } else {
                ApiSetting::create([
                    'key' => $key,
                    'value' => $val,
                    'type' => is_numeric($val) ? 'integer' : 'string',
                    'is_public' => true,
                ]);
            }
        }

        return redirect()->route('admin.api.settings')->with('success', 'API Platform settings updated successfully.');
    }

    /**
     * Registered API Clients Management.
     */
    public function clients()
    {
        $clients = ApiClient::withCount('devices')->latest()->get();

        return view('admin.api.clients', compact('clients'));
    }

    /**
     * Register a new API Client with generated secret.
     */
    public function createClient(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'platform' => 'required|string|in:android,ios,web,third_party',
            'rate_limit_per_minute' => 'required|integer|min:1|max:10000',
            'custom_client_id' => 'nullable|string|max:50|unique:api_clients,client_id',
        ]);

        $customId = trim($request->input('custom_client_id', ''));
        if (!empty($customId)) {
            $clientId = \Illuminate\Support\Str::slug($customId);
        } else {
            $clientId = 'zenx-' . strtolower($request->input('platform')) . '-' . \Illuminate\Support\Str::random(8);
        }

        $plainSecret = 'zenx_sec_' . \Illuminate\Support\Str::random(32);

        $client = ApiClient::create([
            'client_id' => $clientId,
            'client_secret_hash' => hash('sha256', $plainSecret),
            'name' => $request->input('name'),
            'platform' => $request->input('platform'),
            'allowed_origins' => $request->input('allowed_origins') ?: '*',
            'rate_limit_per_minute' => (int)$request->input('rate_limit_per_minute'),
            'is_active' => true,
        ]);

        return redirect()->route('admin.api.clients')
            ->with('success', "New API Client registered! Client ID: {$clientId}")
            ->with('generated_secret', $plainSecret)
            ->with('generated_client_id', $clientId);
    }

    /**
     * Toggle client active status.
     */
    public function toggleClient($id)
    {
        $client = ApiClient::findOrFail($id);
        $client->is_active = !$client->is_active;
        $client->save();

        return redirect()->route('admin.api.clients')
            ->with('success', "Client '{$client->name}' status updated to " . ($client->is_active ? 'Active' : 'Disabled'));
    }

    /**
     * Delete API client.
     */
    public function deleteClient($id)
    {
        $client = ApiClient::findOrFail($id);
        $name = $client->name;
        $client->delete();

        return redirect()->route('admin.api.clients')->with('success', "Client '{$name}' deleted successfully.");
    }

    /**
     * Device Inspector & Management.
     */
    public function devices(Request $request)
    {
        $query = ApiDevice::with('client')->withCount('tokens', 'downloadJobs');

        if ($request->has('search') && !empty($request->query('search'))) {
            $s = trim($request->query('search'));
            $query->where(function ($q) use ($s) {
                $q->where('device_uuid', 'like', "%{$s}%")
                    ->orWhere('device_model', 'like', "%{$s}%")
                    ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        if ($request->has('platform') && !empty($request->query('platform'))) {
            $query->where('platform', $request->query('platform'));
        }

        $devices = $query->latest('last_active_at')->paginate(25);

        return view('admin.api.devices', compact('devices'));
    }

    /**
     * Toggle device blocked status.
     */
    public function toggleBlockDevice(Request $request, $id)
    {
        $device = ApiDevice::findOrFail($id);
        $device->is_blocked = !$device->is_blocked;
        if ($device->is_blocked) {
            $device->block_reason = $request->input('reason', 'Blocked by administrator.');
            // Revoke all tokens
            ApiToken::where('device_id', $device->id)->update(['revoked_at' => Carbon::now()]);
        } else {
            $device->block_reason = null;
        }
        $device->save();

        return back()->with('success', "Device status updated to " . ($device->is_blocked ? 'Blocked' : 'Active'));
    }

    /**
     * Delete device record and associated tokens.
     */
    public function deleteDevice($id)
    {
        $device = ApiDevice::findOrFail($id);
        $device->delete();

        return back()->with('success', 'Device record removed.');
    }

    /**
     * Token Inspector.
     */
    public function tokens(Request $request)
    {
        $query = ApiToken::with('device');

        if ($request->has('status')) {
            if ($request->query('status') === 'active') {
                $query->whereNull('revoked_at')->where('access_expires_at', '>', Carbon::now());
            } elseif ($request->query('status') === 'revoked') {
                $query->whereNotNull('revoked_at');
            } elseif ($request->query('status') === 'expired') {
                $query->where('access_expires_at', '<=', Carbon::now());
            }
        }

        $tokens = $query->latest()->paginate(30);

        return view('admin.api.tokens', compact('tokens'));
    }

    /**
     * Revoke single token.
     */
    public function revokeToken($id)
    {
        $token = ApiToken::findOrFail($id);
        $token->revoke();

        return back()->with('success', 'Token revoked immediately.');
    }

    /**
     * Revoke all tokens for a device.
     */
    public function revokeAllDeviceTokens($deviceId)
    {
        ApiToken::where('device_id', $deviceId)->whereNull('revoked_at')->update(['revoked_at' => Carbon::now()]);

        return back()->with('success', 'All active tokens for device revoked.');
    }

    /**
     * API Telemetry Logs.
     */
    public function logs(Request $request)
    {
        $query = ApiLog::with('device');

        if ($request->has('status') && !empty($request->query('status'))) {
            $status = (int)$request->query('status');
            if ($status === 200) {
                $query->whereBetween('status_code', [200, 299]);
            } elseif ($status === 400) {
                $query->whereBetween('status_code', [400, 499]);
            } elseif ($status === 500) {
                $query->whereBetween('status_code', [500, 599]);
            } else {
                $query->where('status_code', $status);
            }
        }

        if ($request->has('endpoint') && !empty($request->query('endpoint'))) {
            $query->where('endpoint', 'like', '%' . trim($request->query('endpoint')) . '%');
        }

        if ($request->has('ip') && !empty($request->query('ip'))) {
            $query->where('ip_address', 'like', '%' . trim($request->query('ip')) . '%');
        }

        $logs = $query->latest('id')->paginate(50);

        return view('admin.api.logs', compact('logs'));
    }

    /**
     * Purge API Logs.
     */
    public function clearLogs()
    {
        ApiLog::truncate();

        return redirect()->route('admin.api.logs')->with('success', 'All API logs cleared.');
    }

    /**
     * API Interactive Documentation Viewer.
     */
    public function docs()
    {
        $baseUrl = ApiSetting::get('api_base_url', url('/api/v1'));

        return view('admin.api.docs', compact('baseUrl'));
    }
}
