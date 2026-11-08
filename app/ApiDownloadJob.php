<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiDownloadJob extends Model
{
    protected $table = 'api_download_jobs';

    protected $fillable = [
        'job_uuid',
        'device_id',
        'video_url',
        'video_title',
        'format_id',
        'format_type',
        'quality_label',
        'status',
        'download_token',
        'download_token_expires_at',
        'file_path',
        'file_name',
        'file_size',
        'progress',
        'error_message',
        'expires_at',
    ];

    protected $dates = [
        'download_token_expires_at',
        'expires_at',
    ];

    public function device()
    {
        return $this->belongsTo(ApiDevice::class, 'device_id');
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
