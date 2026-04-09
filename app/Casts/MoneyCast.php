<?php

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value to a Money object.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        $currency = $attributes['currency_code']
            ?? $model->organization->currency_code
            ?? config('app.currency', 'NGN');

        return new Money((int) $value, $currency);
    }

    /**
     * Prepare the value for storage (as minor units).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->getMinorAmount();
        }

        // If it's a numeric value (int, float, or numeric string), we treat it as major units
        // and convert it once safely to minor units (multiplied by 100).
        // This ensures consistency when values come from form inputs or DTOs.
        if (is_numeric($value)) {
            return Money::fromMajor($value)->getMinorAmount();
        }

        return $value;
    }
}
