<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'link',
        'type',
        'banner_style',
        'target_interest',
        'is_banner_active',
        'pusher_publish_id',
    ];
}

