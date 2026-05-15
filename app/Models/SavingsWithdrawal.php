<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $reference
 * @property string $savings_account_id
 * @property \Illuminate\Support\Carbon $transaction_date
 * @property \App\ValueObjects\Money $snapshot_balance
 * @property \App\ValueObjects\Money $amount_withdrawn
 * @property \App\ValueObjects\Money $loan_adjustment_amount
 * @property string $status
 * @property string|null $notes
 * @property string $staff_id
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property array<array-key, mixed>|null $audit_trail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read mixed $total_amount
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\SavingsAccount $savingsAccount
 * @property-read \App\Models\User $staff
 * @method static \Database\Factories\SavingsWithdrawalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereAmountWithdrawn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereAuditTrail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereLoanAdjustmentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereSavingsAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereSnapshotBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsWithdrawal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SavingsWithdrawal extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'reference',
        'savings_account_id',
        'transaction_date',
        'snapshot_balance',
        'amount_withdrawn',
        'loan_adjustment_amount',
        'status',
        'notes',
        'staff_id',
        'approved_by',
        'approved_at',
        'audit_trail',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'snapshot_balance' => \App\Casts\MoneyCast::class,
        'amount_withdrawn' => \App\Casts\MoneyCast::class,
        'loan_adjustment_amount' => \App\Casts\MoneyCast::class,
        'approved_at' => 'datetime',
        'audit_trail' => 'array',
    ];

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the total effective withdrawal (cash + loan adjustment).
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount_withdrawn->add($this->loan_adjustment_amount);
    }
}
