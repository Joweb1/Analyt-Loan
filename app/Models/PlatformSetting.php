<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property array<array-key, mixed>|null $value
 * @property string $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
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
