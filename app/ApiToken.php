<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $fillable = [
        'device_id',
        'access_token_hash',
        'refresh_token_hash',
        'access_expires_at',
        'refresh_expires_at',
        'revoked_at',
        'last_used_ip',
    ];

    protected $dates = [
        'access_expires_at',
        'refresh_expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'access_token_hash',
        'refresh_token_hash',
    ];

    public function device()
    {
        return $this->belongsTo(ApiDevice::class, 'device_id');
    }

    public function isAccessExpired()
    {
        return $this->access_expires_at && $this->access_expires_at->isPast();
    }

    public function isRefreshExpired()
    {
        return $this->refresh_expires_at && $this->refresh_expires_at->isPast();
    }

    public function isRevoked()
    {
        return !is_null($this->revoked_at);
    }

    public function revoke()
    {
        $this->update(['revoked_at' => now()]);
    }
}
