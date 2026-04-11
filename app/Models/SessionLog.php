<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    public $timestamps = false; // We use created_at manually via schema default

    protected $fillable = [
        'user_id',
        'session_id',
        'path',
        'method',
        'ip_address',
        'user_agent',
        'csrf_token_session',
        'csrf_token_request',
        'is_authenticated',
        'cookies',
        'payload',
    ];

    protected $casts = [
        'cookies' => 'array',
        'payload' => 'array',
        'is_authenticated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
