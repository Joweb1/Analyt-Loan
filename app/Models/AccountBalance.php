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
 * @property \App\ValueObjects\Money $opening_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccountBalance whereYear($value)
 *
 * @mixin \Eloquent
 */
class AccountBalance extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'month',
        'year',
        'opening_balance',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => MoneyCast::class,
        ];
    }
}
