<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $name
 * @property string|null $description
 * @property numeric|null $default_interest_rate
 * @property int|null $default_duration
 * @property string $duration_unit
 * @property string $repayment_cycle
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereDefaultDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereDefaultInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereDurationUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereRepaymentCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoanProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LoanProduct extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'default_interest_rate',
        'default_duration',
        'duration_unit',
        'repayment_cycle',
    ];
}
