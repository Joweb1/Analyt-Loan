<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $user_id
 * @property string $organization_id
 * @property string|null $portfolio_id
 * @property string|null $custom_id
 * @property string|null $phone
 * @property bool $is_daily_saver
 * @property Money $daily_target_amount
 * @property string $kyc_status
 * @property array|null $custom_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 * @property-read Portfolio|null $portfolio
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereCustomData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereCustomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereDailyTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereIsDailySaver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereKycStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver wherePortfolioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Saver whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Saver extends Model
{
    use HasFactory, HasUuids;

    protected static function booted()
    {
        static::creating(function ($saver) {
            if (empty($saver->custom_id)) {
                $saver->custom_id = 'SAV-'.strtoupper(Str::random(6));
            }
        });
    }

    protected $fillable = [
        'user_id',
        'organization_id',
        'portfolio_id',
        'custom_id',
        'phone',
        'is_daily_saver',
        'daily_target_amount',
        'kyc_status',
        'custom_data',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    protected function casts(): array
    {
        return [
            'custom_data' => 'array',
            'is_daily_saver' => 'boolean',
            'daily_target_amount' => MoneyCast::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
