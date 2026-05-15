<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string|null $organization_id
 * @property string|null $user_id
 * @property string $auditable_type
 * @property string $auditable_id
 * @property string $event
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $auditable
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereAuditableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereAuditableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditTrail whereUserId($value)
 *
 * @mixin \Eloquent
 */
class AuditTrail extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
