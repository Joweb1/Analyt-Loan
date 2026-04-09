<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $borrower_id
 * @property \App\ValueObjects\Money $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $loan_number
 * @property string|null $loan_product
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property numeric $interest_rate
 * @property string $interest_type
 * @property int $duration
 * @property string $duration_unit
 * @property string $repayment_cycle
 * @property int $num_repayments
 * @property \App\ValueObjects\Money|null $processing_fee
 * @property string|null $processing_fee_type
 * @property \App\ValueObjects\Money|null $insurance_fee
 * @property string|null $description
 * @property array<array-key, mixed>|null $attachments
 * @property string $status
 * @property \App\ValueObjects\Money $penalty_value
 * @property string $penalty_type
 * @property string $penalty_frequency
 * @property bool $override_system_penalty
 * @property string|null $organization_id
 * @property string|null $loan_officer_id
 * @property-read \App\Models\Borrower $borrower
 * @property-read \App\Models\Collateral|null $collateral
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\User|null $loanOfficer
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Repayment> $repayments
 * @property-read int|null $repayments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScheduledRepayment> $scheduledRepayments
 * @property-read int|null $scheduled_repayments_count
 *
 * @method static \Database\Factories\LoanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereBorrowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInsuranceFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInterestType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereLoanNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereLoanOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereLoanProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereNumRepayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereOverrideSystemPenalty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan wherePenaltyFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan wherePenaltyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan wherePenaltyValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereProcessingFeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereReleaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereRepaymentCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use Auditable, BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'portfolio_id',
        'borrower_id',
        'loan_officer_id',
        'guarantor_id',
        'external_guarantor_id',
        'amount',
        'loan_number',
        'loan_product',
        'release_date',
        'interest_rate',
        'interest_type',
        'duration',
        'duration_unit',
        'repayment_cycle',
        'num_repayments',
        'processing_fee',
        'processing_fee_type',
        'insurance_fee',
        'penalty_value',
        'penalty_type',
        'penalty_frequency',
        'override_system_penalty',
        'description',
        'attachments',
        'status',
    ];

    protected $appends = [
        'attachment_urls',
    ];

    protected $casts = [
        'release_date' => 'date',
        'amount' => \App\Casts\MoneyCast::class,
        'attachments' => 'array',
        'override_system_penalty' => 'boolean',
        'penalty_value' => \App\Casts\MoneyCast::class,
        'processing_fee' => \App\Casts\MoneyCast::class,
        'insurance_fee' => \App\Casts\MoneyCast::class,
    ];

    public function repayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    public function scheduledRepayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ScheduledRepayment::class)->orderBy('due_date');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function collateral(): HasOne
    {
        return $this->hasOne(Collateral::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guarantor_id');
    }

    public function externalGuarantor(): BelongsTo
    {
        return $this->belongsTo(Guarantor::class, 'external_guarantor_id');
    }

    public function getAttachmentUrlsAttribute(): array
    {
        if (! $this->attachments) {
            return [];
        }

        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return collect($this->attachments)->map(function ($path) use ($disk) {
            return \Illuminate\Support\Facades\Storage::disk($disk)->url($path);
        })->toArray();
    }

    /**
     * Get the total expected interest for the entire duration of the loan.
     */
    public function getTotalExpectedInterest(): \App\ValueObjects\Money
    {
        /** @var \App\ValueObjects\Money $principal */
        $principal = $this->amount;
        $currency = $principal->getCurrency();

        $rate = (string) (($this->interest_rate ?? 0) / 100);
        $duration = (int) ($this->duration ?? 1);
        $durationUnit = $this->duration_unit ?? 'month';
        $interestType = $this->interest_type ?? 'year';

        if ($durationUnit === $interestType) {
            return $principal->multiply(bcmul($rate, (string) $duration, 4));
        }

        // Conversion factors to a base unit (days)
        $conversion = [
            'day' => '1',
            'week' => '7',
            'month' => '30.44', // average month length
            'year' => '365.25',
        ];

        $durationInDays = bcmul((string) $duration, ($conversion[$durationUnit] ?? '30.44'), 4);
        $interestPeriodInDays = $conversion[$interestType] ?? '365.25';

        $multiplier = bcdiv($durationInDays, $interestPeriodInDays, 4);
        $totalMultiplier = bcmul($rate, $multiplier, 4);

        return $principal->multiply($totalMultiplier);
    }

    public function getBalanceAttribute(): \App\ValueObjects\Money
    {
        /** @var \App\ValueObjects\Money $principal */
        $principal = $this->amount;
        $currency = $principal->getCurrency();

        // Use Scheduled Repayments as the source of truth for total due
        $totalDueMinor = (int) $this->scheduledRepayments()
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount) as total')
            ->value('total');

        if ($totalDueMinor > 0) {
            $totalPaidMinor = (int) $this->repayments()->sum('amount');
            $remainingMinor = max(0, $totalDueMinor - $totalPaidMinor);

            return new \App\ValueObjects\Money($remainingMinor, $currency);
        }

        // Fallback if no schedules exist (should not happen in production)
        /** @var \App\ValueObjects\Money $totalInterest */
        $totalInterest = $this->getTotalExpectedInterest();

        /** @var \App\ValueObjects\Money $processingFee */
        $processingFee = $this->processing_fee ?? new \App\ValueObjects\Money(0, $currency);
        /** @var \App\ValueObjects\Money $insuranceFee */
        $insuranceFee = $this->insurance_fee ?? new \App\ValueObjects\Money(0, $currency);

        $totalPayable = $principal->add($totalInterest)->add($processingFee)->add($insuranceFee);
        $totalPaidMinor = (int) $this->repayments()->sum('amount');

        return new \App\ValueObjects\Money(max(0, $totalPayable->getMinorAmount() - $totalPaidMinor), $currency);
    }

    /**
     * Synchronize the status of scheduled repayments based on actual repayments.
     */
    public function refreshRepaymentStatus(): void
    {
        app(\App\Actions\Loans\SynchronizeLoanState::class)->execute($this);
    }
}
