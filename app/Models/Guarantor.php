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
 * @property string|null $user_id
 * @property string|null $custom_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $bvn
 * @property string|null $national_identity_number
 * @property string|null $employer
 * @property numeric|null $income
 * @property array<array-key, mixed>|null $custom_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $portfolio_id
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\Portfolio|null $portfolio
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\GuarantorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereBvn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereCustomData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereCustomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereEmployer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereNationalIdentityNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor wherePortfolioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantor whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Guarantor extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected static function booted()
    {
        static::creating(function ($guarantor) {
            if (empty($guarantor->custom_id)) {
                $guarantor->custom_id = 'GUA-'.strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    protected $fillable = [
        'organization_id',
        'portfolio_id',
        'user_id',
        'custom_id',
        'name',
        'phone',
        'email',
        'address',
        'bvn',
        'national_identity_number',
        'employer',
        'income',
        'custom_data',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    protected $casts = [
        'custom_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
