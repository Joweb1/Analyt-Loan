<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormFieldConfig extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'section',
        'name',
        'label',
        'type',
        'options',
        'is_required',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];
}
