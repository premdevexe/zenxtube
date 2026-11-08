<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    public $timestamps = false; // We use created_at only
    protected $table = 'api_logs';

    protected $fillable = [
        'device_id',
        'endpoint',
        'method',
        'status_code',
        'duration_ms',
        'ip_address',
        'user_agent',
        'request_payload',
        'response_summary',
        'created_at',
    ];

    protected $dates = [
        'created_at',
    ];

    public function device()
    {
        return $this->belongsTo(ApiDevice::class, 'device_id');
    }
}
