<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $user_id
 * @property string $session_id
 * @property string|null $path
 * @property string|null $method
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $csrf_token_session
 * @property string|null $csrf_token_request
 * @property bool $is_authenticated
 * @property array<array-key, mixed>|null $cookies
 * @property array<array-key, mixed>|null $payload
 * @property string $created_at
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereCookies($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereCsrfTokenRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereCsrfTokenSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereIsAuthenticated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SessionLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
