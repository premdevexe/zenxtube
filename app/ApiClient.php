<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    protected $table = 'api_clients';

    protected $fillable = [
        'client_id',
        'client_secret_hash',
        'name',
        'platform',
        'allowed_origins',
        'rate_limit_per_minute',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit_per_minute' => 'integer',
    ];

    protected $hidden = [
        'client_secret_hash',
    ];

    public function devices()
    {
        return $this->hasMany(ApiDevice::class, 'client_id');
    }

    /**
     * Check if a given secret matches the hashed secret.
     */
    public function verifySecret($secret)
    {
        return hash_equals($this->client_secret_hash, hash('sha256', $secret));
    }
}
