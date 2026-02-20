<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $loan_id
 * @property \Illuminate\Support\Carbon $due_date
 * @property numeric $principal_amount
 * @property numeric $interest_amount
 * @property numeric $penalty_amount
 * @property numeric $paid_amount
 * @property string $status
 * @property int $installment_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Loan $loan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereInstallmentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereLoanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment wherePenaltyAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment wherePrincipalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ScheduledRepayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ScheduledRepayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'loan_id',
        'due_date',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'paid_amount',
        'status',
        'installment_number',
    ];

    protected $casts = [
        'due_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
