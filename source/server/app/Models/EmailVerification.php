<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class EmailVerification extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'email_verifications';

    protected $fillable = [
        'email',
        'code',
        'name',
        'password',
        'expires_at',
        'verified',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified'   => 'boolean',
    ];
}