<?php

namespace App\Models;

use App\Services\TenantSession;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $logo_path
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string $status
 * @property string $kyc_status
 * @property string|null $rejection_reason
 * @property string|null $owner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $kyc_document_path
 * @property numeric $default_interest_rate
 * @property int $grace_period_days
 * @property string $currency_code
 * @property string $timezone
 * @property bool $email_reminders_enabled
 * @property bool $loan_approval_alerts_enabled
 * @property string|null $signature_path
 * @property string|null $rc_number
 * @property bool $push_notifications_enabled
 * @property bool $repayment_notifications_enabled
 * @property bool $overdue_notifications_enabled
 * @property bool $new_borrower_notifications_enabled
 * @property bool $allow_flexible_repayments
 * @property int $thrift_cycle_days
 * @property string|null $tagline
 * @property string $brand_color
 * @property string|null $repayment_bank_name
 * @property string|null $repayment_account_number
 * @property string|null $repayment_account_name
 * @property numeric|null $total_lent
 * @property numeric|null $total_collected
 * @property numeric|null $monthly_lent
 * @property numeric|null $monthly_collected
 * @property int|null $active_loans_count
 * @property-read Collection<int, Borrower> $borrowers
 * @property-read int|null $borrowers_count
 * @property-read Collection<int, Collateral> $collaterals
 * @property-read int|null $collaterals_count
 * @property-read Collection<int, Loan> $loans
 * @property-read int|null $loans_count
 * @property-read User|null $owner
 * @property-read Collection<int, SavingsAccount> $savingsAccounts
 * @property-read int|null $savings_accounts_count
 * @property-read Collection<int, User> $staff
 * @property-read int|null $staff_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Database\Factories\OrganizationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereAllowFlexibleRepayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereBrandColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDefaultInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereEmailRemindersEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereGracePeriodDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereKycDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereKycStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereLoanApprovalAlertsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereNewBorrowerNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereOverdueNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization wherePushNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRcNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRepaymentAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRepaymentAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRepaymentBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRepaymentNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSignaturePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereWebsite($value)
 *
 * @property Carbon|null $system_date
 * @property string $default_customer_password
 * @property-read Collection<int, User> $customers
 * @property-read int|null $customers_count
 * @property-read string|null $kyc_document_url
 * @property-read string|null $logo_url
 * @property-read Money $organization_balance
 * @property-read string|null $signature_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereDefaultCustomerPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereSystemDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereThriftCycleDays($value)
 *
 * @mixin \Eloquent
 */
class Organization extends Model
{
    use HasFactory, HasUuids;

    protected static function booted()
    {
        static::creating(function ($organization) {
            if (empty($organization->system_date)) {
                $organization->system_date = now()->toDateString();
            }
        });

        static::updated(function ($organization) {
            Cache::forget("organization_{$organization->id}");
        });
    }

    protected $fillable = [
        'name',
        'rc_number',
        'slug',
        'logo_path',
        'signature_path',
        'address',
        'phone',
        'email',
        'website',
        'status',
        'kyc_status',
        'rejection_reason',
        'owner_id',
        'email_reminders_enabled',
        'loan_approval_alerts_enabled',
        'push_notifications_enabled',
        'repayment_notifications_enabled',
        'overdue_notifications_enabled',
        'new_borrower_notifications_enabled',
        'allow_flexible_repayments',
        'cashbook_unlock_limit',
        'allow_staff_cashbook_unlock',
        'live_balance_visibility_enabled',
        'thrift_cycle_days',
        'tagline',
        'brand_color',
        'repayment_bank_name',
        'repayment_account_number',
        'repayment_account_name',
        'default_interest_rate',
        'interest_calculation_type',
        'system_date',
        'timezone',
    ];

    protected $appends = [
        'logo_url',
        'signature_url',
        'kyc_document_url',
    ];

    public function setInterestCalculationTypeAttribute($value)
    {
        $this->attributes['interest_calculation_type'] = $value ? strtolower($value) : 'percentage';
    }

    /**
     * Get the current system time for this organization.
     * Combines the pinned system_date with the exact real-world time.
     */
    public function getSystemTime(): Carbon
    {
        $tz = $this->timezone ?: config('app.timezone', 'UTC');
        $realNow = Carbon::createFromTimestamp(time(), $tz);

        if ($this->system_date) {
            // Return the system date but with the real hours/mins/secs
            return Carbon::parse($this->system_date, $tz)
                ->setTimeFrom($realNow);
        }

        return $realNow;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_reminders_enabled' => 'boolean',
            'loan_approval_alerts_enabled' => 'boolean',
            'push_notifications_enabled' => 'boolean',
            'repayment_notifications_enabled' => 'boolean',
            'overdue_notifications_enabled' => 'boolean',
            'new_borrower_notifications_enabled' => 'boolean',
            'allow_flexible_repayments' => 'boolean',
            'allow_staff_cashbook_unlock' => 'boolean',
            'live_balance_visibility_enabled' => 'boolean',
            'cashbook_unlock_limit' => 'integer',
            'thrift_cycle_days' => 'integer',
            'system_date' => 'date',
            'default_interest_rate' => 'decimal:2',
            'grace_period_days' => 'integer',
        ];
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value ? strtolower($value) : 'active';
    }

    public function setKycStatusAttribute($value)
    {
        $this->attributes['kyc_status'] = $value ? strtolower($value) : 'pending';
    }

    public static function current(bool $fresh = false): ?self
    {
        $tenantSession = app(TenantSession::class);
        $orgId = $tenantSession->getTenantId();

        if (! $orgId) {
            return null;
        }

        if ($fresh) {
            Cache::forget("organization_{$orgId}");
        }

        return Cache::remember("organization_{$orgId}", 3600, function () use ($orgId) {
            return self::find($orgId);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('type', ['admin', 'staff']);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(User::class)->where('type', 'customer');
    }

    public function borrowers(): HasMany
    {
        return $this->hasMany(Borrower::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function savingsAccounts(): HasMany
    {
        return $this->hasMany(SavingsAccount::class);
    }

    public function collaterals(): HasMany
    {
        return $this->hasMany(Collateral::class);
    }

    /**
     * Total Organization Portfolio Balance: total loaned + interest - repayments
     */
    public function getOrganizationBalanceAttribute(): Money
    {
        $loans = $this->loans()->whereNotIn('status', ['draft', 'rejected', 'applied'])->get();
        $currency = $this->currency_code ?: config('app.currency', 'NGN');

        $totalValueMinor = 0;
        foreach ($loans as $loan) {
            /** @var Loan $loan */
            $totalValueMinor += $loan->getTotalCost()->getMinorAmount();
        }

        $totalCollectedMinor = (int) Repayment::whereIn('loan_id', $loans->pluck('id'))
            ->sum('amount');

        return new Money($totalValueMinor - $totalCollectedMinor, $currency);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return Storage::disk($disk)->url($this->logo_path);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return Storage::disk($disk)->url($this->signature_path);
    }

    public function getKycDocumentUrlAttribute(): ?string
    {
        if (! $this->kyc_document_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return Storage::disk($disk)->url($this->kyc_document_path);
    }
}
