<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'custom_data' => 'array',
    ];
}
