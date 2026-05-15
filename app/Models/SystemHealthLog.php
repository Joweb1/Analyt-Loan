<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $level
 * @property string $component
 * @property string $message
 * @property array<array-key, mixed>|null $payload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $organization_id
 * @property-read \App\Models\Organization|null $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereComponent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemHealthLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SystemHealthLog extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'level',
        'component',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
