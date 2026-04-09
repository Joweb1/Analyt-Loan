<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Borrower> $borrowers
 * @property-read int|null $borrowers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Collateral> $collaterals
 * @property-read int|null $collaterals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Loan> $loans
 * @property-read int|null $loans_count
 * @property-read \App\Models\User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SavingsAccount> $savingsAccounts
 * @property-read int|null $savings_accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $staff
 * @property-read int|null $staff_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
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
 * @mixin \Eloquent
 */
class Organization extends Model
{
    use HasFactory, HasUuids;

    protected static function booted()
    {
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
        'tagline',
        'brand_color',
        'repayment_bank_name',
        'repayment_account_number',
        'repayment_account_name',
        'use_manual_date',
        'operating_date',
        'timezone',
    ];

    protected $appends = [
        'logo_url',
        'signature_url',
        'kyc_document_url',
    ];

    /**
     * Get the current system time for this organization.
     * Bypasses Carbon::setTestNow() and uses the DB stored value if manual date is enabled.
     */
    public function getSystemTime(): \Illuminate\Support\Carbon
    {
        $tz = $this->timezone ?: config('app.timezone', 'UTC');

        if ($this->use_manual_date && $this->operating_date) {
            // We return the operating date at the start of day in the org's timezone
            return \Illuminate\Support\Carbon::parse($this->operating_date)
                ->setTimezone($tz)
                ->startOfDay();
        }

        return \Illuminate\Support\Carbon::now($tz);
    }

    /**
     * Static helper to get current org's system time.
     */
    public static function systemNow(): \Illuminate\Support\Carbon
    {
        $current = self::current();
        if ($current) {
            return $current->getSystemTime();
        }

        return \Illuminate\Support\Carbon::now();
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
            'use_manual_date' => 'boolean',
            'operating_date' => 'datetime',
            'default_interest_rate' => 'decimal:2',
            'grace_period_days' => 'integer',
        ];
    }

    public static function current(bool $fresh = false): ?self
    {
        $tenantSession = app(\App\Services\TenantSession::class);
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
        return $this->hasMany(User::class)->whereHas('roles', function ($q) {
            $q->where('name', '!=', 'Borrower');
        });
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
    public function getOrganizationBalanceAttribute(): \App\ValueObjects\Money
    {
        $loans = $this->loans()->whereNotIn('status', ['draft', 'rejected', 'applied'])->get();
        $currency = $this->currency_code ?: config('app.currency', 'NGN');

        $totalLoaned = new \App\ValueObjects\Money(0, $currency);
        foreach ($loans as $loan) {
            $totalLoaned = $totalLoaned->add($loan->amount ?? new \App\ValueObjects\Money(0, $currency));
        }

        $totalInterest = new \App\ValueObjects\Money(0, $currency);
        foreach ($loans as $loan) {
            /** @var \App\Models\Loan $loan */
            $totalInterest = $totalInterest->add($loan->getTotalExpectedInterest());
        }

        $totalCollectedMinor = (int) Repayment::whereIn('loan_id', $loans->pluck('id'))
            ->sum('amount');
        $totalCollected = new \App\ValueObjects\Money($totalCollectedMinor, $currency);

        return $totalLoaned->add($totalInterest)->subtract($totalCollected);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->logo_path);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->signature_path);
    }

    public function getKycDocumentUrlAttribute(): ?string
    {
        if (! $this->kyc_document_path) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->kyc_document_path);
    }
}
