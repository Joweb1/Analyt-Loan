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
 * @property numeric $amount
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
 * @property numeric|null $processing_fee
 * @property string|null $processing_fee_type
 * @property numeric|null $insurance_fee
 * @property string|null $description
 * @property array<array-key, mixed>|null $attachments
 * @property string $status
 * @property numeric $penalty_value
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
        'amount' => 'decimal:2',
        'attachments' => 'array',
        'override_system_penalty' => 'boolean',
        'penalty_value' => 'decimal:2',
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
    public function getTotalExpectedInterest(): float
    {
        $principal = (float) $this->amount;
        $rate = (float) ($this->interest_rate ?? 0) / 100;
        $duration = (int) ($this->duration ?? 1);
        $durationUnit = $this->duration_unit ?? 'month';
        $interestType = $this->interest_type ?? 'year';

        if ($durationUnit === $interestType) {
            return round($principal * $rate * $duration, 2);
        }

        // Conversion factors to a base unit (days)
        $conversion = [
            'day' => 1,
            'week' => 7,
            'month' => 30.44, // average month length
            'year' => 365.25,
        ];

        $durationInDays = $duration * ($conversion[$durationUnit] ?? 30.44);
        $interestPeriodInDays = $conversion[$interestType] ?? 365.25;

        $multiplier = $durationInDays / $interestPeriodInDays;

        return round($principal * $rate * $multiplier, 2);
    }

    public function getBalanceAttribute(): float
    {
        $totalInterest = $this->getTotalExpectedInterest();
        $totalFees = (float) ($this->processing_fee ?? 0) + (float) ($this->insurance_fee ?? 0);
        $totalPenalties = (float) $this->scheduledRepayments()->sum('penalty_amount');

        // Note: processing_fee and insurance_fee are already added to penalty_amount of the first schedule
        // during schedule generation. To avoid double counting, we check if they are already accounted for.
        // Actually, better logic: sum(principal) + sum(interest) + sum(penalty) - totalPaid.

        $totalPrincipal = (float) $this->amount;
        $totalPayable = $totalPrincipal + $totalInterest + max($totalFees, $totalPenalties);

        // Let's use a more robust way:
        $totalDueFromSchedules = (float) $this->scheduledRepayments()->sum(\Illuminate\Support\Facades\DB::raw('principal_amount + interest_amount + penalty_amount'));

        if ($totalDueFromSchedules > 0) {
            $totalPaid = (float) $this->repayments()->sum('amount');

            return max(0, round($totalDueFromSchedules - $totalPaid, 2));
        }

        $totalPayable = (float) $this->amount + $totalInterest + $totalFees;
        $totalPaid = (float) $this->repayments()->sum('amount');

        return max(0, round($totalPayable - $totalPaid, 2));
    }

    /**
     * Synchronize the status of scheduled repayments based on actual repayments.
     */
    public function refreshRepaymentStatus(): void
    {
        app(\App\Actions\Loans\SynchronizeLoanState::class)->execute($this);
    }
}
