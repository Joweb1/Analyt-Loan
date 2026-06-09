<?php

namespace App\Models;

use App\Actions\Loans\SynchronizeLoanState;
use App\Casts\MoneyCast;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\ValueObjects\Money;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $borrower_id
 * @property Money $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $loan_number
 * @property string|null $loan_product
 * @property Carbon|null $release_date
 * @property numeric $interest_rate
 * @property string $interest_calculation_type
 * @property string $interest_type
 * @property int $duration
 * @property string $duration_unit
 * @property string $repayment_cycle
 * @property int $num_repayments
 * @property Money|null $processing_fee
 * @property string|null $processing_fee_type
 * @property Money|null $insurance_fee
 * @property string|null $insurance_fee_type
 * @property string|null $description
 * @property array<array-key, mixed>|null $attachments
 * @property string $status
 * @property Money $penalty_value
 * @property string $penalty_type
 * @property string $penalty_frequency
 * @property bool $override_system_penalty
 * @property string|null $organization_id
 * @property string|null $loan_officer_id
 * @property-read Borrower $borrower
 * @property-read Collateral|null $collateral
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read User|null $loanOfficer
 * @property-read Organization|null $organization
 * @property-read Collection<int, Repayment> $repayments
 * @property-read int|null $repayments_count
 * @property-read Collection<int, ScheduledRepayment> $scheduledRepayments
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
 * @property string|null $guarantor_id
 * @property string|null $external_guarantor_id
 * @property string|null $portfolio_id
 * @property Carbon|null $installment_date
 * @property string|null $register_notes
 * @property-read Collection<int, AuditTrail> $auditTrails
 * @property-read int|null $audit_trails_count
 * @property-read Guarantor|null $externalGuarantor
 * @property-read array $attachment_urls
 * @property-read Money $balance
 * @property-read User|null $guarantor
 * @property-read Portfolio|null $portfolio
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereExternalGuarantorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereGuarantorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereInstallmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan wherePortfolioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Loan whereRegisterNotes($value)
 *
 * @mixin \Eloquent
 */
class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
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
        'interest_calculation_type',
        'interest_type',
        'duration',
        'duration_unit',
        'repayment_cycle',
        'num_repayments',
        'processing_fee',
        'processing_fee_type',
        'insurance_fee',
        'insurance_fee_type',
        'penalty_value',
        'penalty_type',
        'penalty_frequency',
        'override_system_penalty',
        'description',
        'attachments',
        'status',
        'installment_date',
        'register_notes',
    ];

    protected $appends = [
        'attachment_urls',
    ];

    protected $casts = [
        'release_date' => 'date',
        'installment_date' => 'date',
        'amount' => MoneyCast::class,
        'attachments' => 'array',
        'override_system_penalty' => 'boolean',
        'penalty_value' => MoneyCast::class,
        'processing_fee' => MoneyCast::class,
        'insurance_fee' => MoneyCast::class,
    ];

    public function setInterestTypeAttribute($value)
    {
        $this->attributes['interest_type'] = $value ? strtolower($value) : 'year';
    }

    public function setInterestCalculationTypeAttribute($value)
    {
        $this->attributes['interest_calculation_type'] = $value ? strtolower($value) : 'percentage';
    }

    public function setDurationUnitAttribute($value)
    {
        $this->attributes['duration_unit'] = $value ? strtolower($value) : 'month';
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value ? strtolower($value) : 'applied';
    }

    public function setProcessingFeeTypeAttribute($value)
    {
        $this->attributes['processing_fee_type'] = $value ? strtolower($value) : 'fixed';
    }

    public function setInsuranceFeeTypeAttribute($value)
    {
        $this->attributes['insurance_fee_type'] = $value ? strtolower($value) : 'fixed';
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    /**
     * @return HasMany<ScheduledRepayment, $this>
     */
    public function scheduledRepayments(): HasMany
    {
        return $this->hasMany(ScheduledRepayment::class)->orderBy('due_date');
    }

    public function comments(): MorphMany
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

        $disk = config('filesystems.disks.supabase.is_configured')
            ? 'supabase'
            : (config('filesystems.default') === 'local' ? 'public' : config('filesystems.default'));

        return collect($this->attachments)->map(function ($path) use ($disk) {
            return Storage::disk($disk)->url($path);
        })->toArray();
    }

    /**
     * Get the total expected interest for the loan (Flat model).
     * Now independent of duration.
     */
    public function getTotalExpectedInterest(): Money
    {
        $principal = $this->amount;
        $currency = $principal->getCurrency();

        if ($this->interest_calculation_type === 'fixed') {
            return new Money((int) bcmul((string) ($this->interest_rate ?? 0), '100', 0), $currency);
        }

        // Percentage logic: Principal * (Rate / 100)
        $rate = (string) (($this->interest_rate ?? 0) / 100);

        return $principal->multiply($rate);
    }

    /**
     * Get the total cost of the loan (Principal + Total Interest + Fees).
     */
    public function getTotalCost(): Money
    {
        $totalInterest = $this->getTotalExpectedInterest();
        $processingFee = $this->getCalculatedProcessingFee();
        $insuranceFee = $this->getCalculatedInsuranceFee();

        return $this->amount
            ->add($totalInterest)
            ->add($processingFee)
            ->add($insuranceFee);
    }

    /**
     * Calculate the insurance fee based on its type (fixed or percentage).
     * Now follows the same distributed model as interest.
     */
    public function getCalculatedInsuranceFee(): Money
    {
        $currency = $this->amount->getCurrency();

        if (! $this->insurance_fee || $this->insurance_fee->isZero()) {
            return new Money(0, $currency);
        }

        if ($this->insurance_fee_type === 'percentage') {
            // When stored as a percentage, the 'major amount' is the percentage value
            $percentage = $this->insurance_fee->getMajorAmount();

            return $this->amount->multiply($percentage / 100);
        }

        return $this->insurance_fee;
    }

    /**
     * Calculate the processing fee based on its type (fixed or percentage).
     */
    public function getCalculatedProcessingFee(): Money
    {
        $currency = $this->amount->getCurrency();

        if (! $this->processing_fee || $this->processing_fee->isZero()) {
            return new Money(0, $currency);
        }

        if ($this->processing_fee_type === 'percentage') {
            // When stored as a percentage, the 'major amount' is the percentage value (e.g., 2.5 for 2.5%)
            $percentage = $this->processing_fee->getMajorAmount();

            return $this->amount->multiply($percentage / 100);
        }

        return $this->processing_fee;
    }

    public function getBalanceAttribute(): Money
    {
        /** @var Money $principal */
        $principal = $this->amount;
        $currency = $principal->getCurrency();

        // Use Scheduled Repayments as the source of truth for total due
        $totalDueMinor = (int) $this->scheduledRepayments()
            ->reorder() // Remove any default order from the relationship
            ->selectRaw('SUM(principal_amount + interest_amount + penalty_amount) as total')
            ->value('total');

        if ($totalDueMinor > 0) {
            $totalPaidMinor = (int) $this->repayments()->sum('amount');
            $remainingMinor = max(0, $totalDueMinor - $totalPaidMinor);

            return new Money($remainingMinor, $currency);
        }

        // Fallback if no schedules exist (should not happen in production)
        /** @var Money $totalInterest */
        $totalInterest = $this->getTotalExpectedInterest();

        /** @var Money $processingFee */
        $processingFee = $this->getCalculatedProcessingFee();
        /** @var Money $insuranceFee */
        $insuranceFee = $this->getCalculatedInsuranceFee();

        $totalPayable = $principal->add($totalInterest)->add($processingFee)->add($insuranceFee);
        $totalPaidMinor = (int) $this->repayments()->sum('amount');

        return new Money(max(0, $totalPayable->getMinorAmount() - $totalPaidMinor), $currency);
    }

    /**
     * Synchronize the status of scheduled repayments based on actual repayments.
     */
    public function refreshRepaymentStatus(): void
    {
        app(SynchronizeLoanState::class)->execute($this);
    }
}
