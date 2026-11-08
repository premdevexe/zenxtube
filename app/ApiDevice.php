<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiDevice extends Model
{
    protected $table = 'api_devices';

    protected $fillable = [
        'device_uuid',
        'client_id',
        'platform',
        'device_model',
        'os_version',
        'app_version',
        'ip_address',
        'fcm_token',
        'is_blocked',
        'block_reason',
        'last_active_at',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    protected $dates = [
        'last_active_at',
    ];

    public function client()
    {
        return $this->belongsTo(ApiClient::class, 'client_id');
    }

    public function tokens()
    {
        return $this->hasMany(ApiToken::class, 'device_id');
    }

    public function downloadJobs()
    {
        return $this->hasMany(ApiDownloadJob::class, 'device_id');
    }

    public function logs()
    {
        return $this->hasMany(ApiLog::class, 'device_id');
    }

    public function activeToken()
    {
        return $this->hasOne(ApiToken::class, 'device_id')
            ->whereNull('revoked_at')
            ->where('access_expires_at', '>', now())
            ->latest();
    }
}
