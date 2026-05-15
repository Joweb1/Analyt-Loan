<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $month
 * @property int $year
 * @property \App\ValueObjects\Money $total_budget_amount
 * @property \App\ValueObjects\Money $spent_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $remaining
 * @property-read \App\Models\Organization $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereSpentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereTotalBudgetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseBudget whereYear($value)
 * @mixin \Eloquent
 */
class ExpenseBudget extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'month',
        'year',
        'total_budget_amount',
        'spent_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_budget_amount' => MoneyCast::class,
            'spent_amount' => MoneyCast::class,
        ];
    }

    /**
     * Get the remaining budget.
     */
    public function getRemainingAttribute()
    {
        return $this->total_budget_amount->subtract($this->spent_amount);
    }
}
