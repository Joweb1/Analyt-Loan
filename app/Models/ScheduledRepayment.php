<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\BelongsToOrganization;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $loan_id
 * @property Carbon $due_date
 * @property Money $principal_amount
 * @property Money $interest_amount
 * @property Money $penalty_amount
 * @property Money $paid_amount
 * @property string $status
 * @property int $installment_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Loan $loan
 *
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
 * @method static \Database\Factories\ScheduledRepaymentFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class ScheduledRepayment extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
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
        'principal_amount' => MoneyCast::class,
        'interest_amount' => MoneyCast::class,
        'penalty_amount' => MoneyCast::class,
        'paid_amount' => MoneyCast::class,
    ];

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value ? strtolower($value) : 'applied';
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
