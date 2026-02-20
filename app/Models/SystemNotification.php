<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string $title
 * @property string $message
 * @property string $type
 * @property string|null $category
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $organization_id
 * @property int $is_actionable
 * @property string|null $action_link
 * @property string $priority
 * @property string|null $recipient_id
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $recipient
 * @property-read Model|\Eloquent|null $subject
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification unread()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereActionLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereIsActionable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotification whereUserId($value)
 * @mixin \Eloquent
 */
class SystemNotification extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'user_id',
        'recipient_id',
        'title',
        'message',
        'type',
        'category',
        'is_actionable',
        'action_link',
        'priority',
        'subject_id',
        'subject_type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
