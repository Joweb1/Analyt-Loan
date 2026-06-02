<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $section
 * @property string $name
 * @property string $label
 * @property string $type
 * @property array<array-key, mixed>|null $options
 * @property bool $is_required
 * @property bool $is_active
 * @property bool $is_system
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereUpdatedAt($value)
 *
 * @property string $form_type
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FormFieldConfig whereFormType($value)
 *
 * @mixin \Eloquent
 */
class FormFieldConfig extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'form_type',
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
