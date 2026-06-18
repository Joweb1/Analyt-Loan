<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToOrganization;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string $name
 * @property string|null $email
 * @property string $role
 * @property string|null $branch_id
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $organization_id
 * @property string|null $phone
 * @property Carbon|null $last_login_at
 * @property array<array-key, mixed>|null $settings
 * @property Carbon|null $last_seen_at
 * @property-read Collection<int, Loan> $assignedLoans
 * @property-read int|null $assigned_loans_count
 * @property-read Borrower|null $borrower
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Organization|null $organization
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, PushSubscription> $pushSubscriptions
 * @property-read int|null $push_subscriptions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 *
 * @property string $type
 * @property-read Guarantor|null $guarantor
 * @property-read Collection<int, Portfolio> $portfolios
 * @property-read int|null $portfolios_count
 * @property-read Saver|null $saver
 * @property-read SavingsAccount|null $savingsAccount
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereType($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, HasUuids, Notifiable;

    protected static function booted()
    {
        static::saving(function ($user) {
            if (empty($user->email)) {
                $phone = $user->phone ? preg_replace('/[^0-9]/', '', $user->phone) : Str::random(10);
                $user->email = $phone.'@analytloan.com';
            }
        });
    }

    /**
     * The attributes that are mass assignable.
...
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'type',
        'name',
        'email',
        'phone',
        'password',
        'branch_id',
        'settings',
        'last_login_at',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ? strtolower($value) : 'customer';
    }

    public function isOnline(): bool
    {
        if (! $this->last_seen_at) {
            return false;
        }

        return $this->last_seen_at->gt(now()->subMinutes(5));
    }

    public function pushEnabled(): bool
    {
        return (bool) ($this->settings['push_enabled'] ?? true);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function borrower(): HasOne
    {
        return $this->hasOne(Borrower::class);
    }

    public function saver(): HasOne
    {
        return $this->hasOne(Saver::class);
    }

    public function guarantor(): HasOne
    {
        return $this->hasOne(Guarantor::class);
    }

    public function savingsAccount(): HasOne
    {
        return $this->hasOne(SavingsAccount::class);
    }

    public function assignedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'loan_officer_id');
    }

    public function portfolios(): BelongsToMany
    {
        return $this->belongsToMany(Portfolio::class, 'portfolio_user');
    }

    public function isAppOwner(): bool
    {
        return $this->type === 'owner' || $this->email === config('app.owner');
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->type === 'staff';
    }

    public function isCustomer(): bool
    {
        return $this->type === 'customer';
    }

    public function isBorrower(): bool
    {
        return $this->hasRole('Borrower');
    }

    public function isSaver(): bool
    {
        return $this->hasRole('Saver');
    }

    public function isOrgOwner(): bool
    {
        return $this->organization && $this->organization->owner_id === $this->id;
    }

    /**
     * Override freshTimestamp to ensure User timestamps (created_at, updated_at)
     * always use real-world time, ignoring the simulated System Date.
     */
    public function freshTimestamp()
    {
        return new Carbon(new \DateTime);
    }
}
