<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProcessedRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'idempotency_key',
        'user_id',
        'status_code',
        'response_body',
    ];

    protected $casts = [
        'response_body' => 'array',
    ];
}
