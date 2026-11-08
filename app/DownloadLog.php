<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    protected $table = 'download_logs';

    protected $fillable = [
        'video_url',
        'video_title',
        'video_thumbnail',
        'format_label',
        'type',
        'height',
        'ip_address',
        'user_agent'
    ];
}

