<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Bắt buộc dùng Model của MongoDB
use Laravel\Sanctum\Contracts\HasAbilities;

class PersonalAccessToken extends Model implements HasAbilities
{
    // Chỉ định rõ collection trong MongoDB
    protected $collection = 'personal_access_tokens'; 

    protected $guarded = [];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    public function can($ability)
    {
        return in_array('*', $this->abilities) || in_array($ability, $this->abilities);
    }

    public function cant($ability)
    {
        return ! $this->can($ability);
    }
}