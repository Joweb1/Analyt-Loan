<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $idempotency_key
 * @property string|null $user_id
 * @property int $status_code
 * @property array<array-key, mixed> $response_body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereIdempotencyKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereResponseBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProcessedRequest whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
