<?php

namespace App\Models;

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
 * @mixin \Eloquent
 */
class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'borrower_id',
        'loan_officer_id',
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

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    /**
     * Synchronize the status of scheduled repayments based on actual repayments.
     */
    public function refreshRepaymentStatus(): void
    {
        $repayments = $this->repayments()->orderBy('paid_at')->get();
        $schedules = $this->scheduledRepayments()->orderBy('due_date')->get();

        $totalPaid = $repayments->sum('amount');
        $remaining = $totalPaid;

        foreach ($schedules as $s) {
            /** @var \App\Models\ScheduledRepayment $s */
            $totalDue = ($s->principal_amount ?? 0) + ($s->interest_amount ?? 0) + ($s->penalty_amount ?? 0);

            if ($remaining >= $totalDue && $totalDue > 0) {
                $s->paid_amount = $totalDue;
                $s->status = 'paid';
                $remaining -= $totalDue;
            } elseif ($remaining > 0) {
                $s->paid_amount = $remaining;
                $s->status = 'partial';
                $remaining = 0;
            } else {
                $s->paid_amount = 0;
                $s->status = $s->due_date->isPast() ? 'overdue' : 'pending';
            }
            $s->save();
        }

        // Also sync overall loan status
        $totalInterest = $this->amount * (($this->interest_rate ?? 0) / 100);
        $totalPayable = $this->amount + $totalInterest;

        if ($totalPaid >= $totalPayable && $totalPayable > 0) {
            if ($this->status !== 'repaid') {
                $this->update(['status' => 'repaid']);
            }
        } elseif ($this->status === 'repaid') {
            $this->update(['status' => 'active']);
        }
    }
}
