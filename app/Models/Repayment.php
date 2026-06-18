<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $loan_id
 * @property string|null $borrower_id
 * @property string|null $organization_id
 * @property Money $amount
 * @property Carbon $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $payment_method
 * @property string|null $collected_by
 * @property string|null $notes
 * @property string|null $recorded_by
 * @property Money $principal_amount
 * @property Money $interest_amount
 * @property Money $extra_amount
 * @property-read User|null $collector
 * @property-read Loan $loan
 *
 * @method static \Database\Factories\RepaymentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereCollectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereExtraAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereLoanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment wherePrincipalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereUpdatedAt($value)
 *
 * @property Money $fee_amount
 * @property-read Collection<int, AuditTrail> $auditTrails
 * @property-read int|null $audit_trails_count
 * @property-read Borrower|null $borrower
 * @property-read Organization|null $organization
 * @property-read Collection<int, SavingsTransaction> $savingsTransactions
 * @property-read int|null $savings_transactions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereBorrowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereFeeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repayment whereRecordedBy($value)
 *
 * @mixin \Eloquent
 */
class Repayment extends Model
{
    use Auditable, BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'loan_id',
        'borrower_id',
        'organization_id',
        'amount',
        'payment_method',
        'collected_by',
        'notes',
        'recorded_by',
        'principal_amount',
        'interest_amount',
        'fee_amount',
        'extra_amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => MoneyCast::class,
        'principal_amount' => MoneyCast::class,
        'interest_amount' => MoneyCast::class,
        'fee_amount' => MoneyCast::class,
        'extra_amount' => MoneyCast::class,
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function savingsTransactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    /**
     * Check if the repayment is locked for editing/deletion by the given user.
     */
    public function isLocked(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (! $user) {
            return true;
        }

        // Admins and Owners can always edit
        if ($user->hasRole('Admin') || $user->type === 'owner') {
            return false;
        }

        $loan = $this->loan;
        if (! $loan) {
            return false;
        }

        // Logic: Once a repayment is logged for the period, it is locked for non-admins.
        // We define the period based on the loan's cycle.
        $paidAt = $this->paid_at;
        $now = now();

        if ($loan->repayment_cycle === 'monthly') {
            // Locked if paid in current month or past months
            return $paidAt->format('Y-m') <= $now->format('Y-m');
        }

        // Weekly/Biweekly/Daily: Locked if paid in current week or past weeks
        $startOfWeek = $now->copy()->startOfWeek();

        return $paidAt->lt($startOfWeek->copy()->addWeeks(1));
    }
}
