<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property array<array-key, mixed>|null $value
 * @property string $type
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformSetting whereValue($value)
 *
 * @mixin \Eloquent
 */
class PlatformSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    protected $casts = [
        'value' => 'json',
    ];

    public static function get($key, $default = null)
    {
        return Cache::remember("platform_setting_{$key}", now()->addDay(), function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        Cache::forget("platform_setting_{$key}");

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
