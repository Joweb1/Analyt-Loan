<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $loan_id
 * @property \App\ValueObjects\Money $amount
 * @property \Illuminate\Support\Carbon $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $payment_method
 * @property string|null $collected_by
 * @property \App\ValueObjects\Money $principal_amount
 * @property \App\ValueObjects\Money $interest_amount
 * @property \App\ValueObjects\Money $extra_amount
 * @property-read \App\Models\User|null $collector
 * @property-read \App\Models\Loan $loan
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
 * @mixin \Eloquent
 */
class Repayment extends Model
{
    use Auditable, HasFactory, HasUuids;

    protected $fillable = [
        'loan_id',
        'amount',
        'payment_method',
        'collected_by',
        'principal_amount',
        'interest_amount',
        'extra_amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => \App\Casts\MoneyCast::class,
        'principal_amount' => \App\Casts\MoneyCast::class,
        'interest_amount' => \App\Casts\MoneyCast::class,
        'extra_amount' => \App\Casts\MoneyCast::class,
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function savingsTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }
}
