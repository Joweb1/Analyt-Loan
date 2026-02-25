<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property numeric $value
 * @property string|null $image_path
 * @property string|null $loan_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type
 * @property string|null $condition
 * @property array<array-key, mixed>|null $documents
 * @property \Illuminate\Support\Carbon|null $registered_date
 * @property string|null $organization_id
 * @property-read \App\Models\Loan|null $loan
 * @property-read \App\Models\Organization|null $organization
 *
 * @method static \Database\Factories\CollateralFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereLoanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereRegisteredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Collateral whereValue($value)
 *
 * @mixin \Eloquent
 */
class Collateral extends Model
{
    /** @use HasFactory<\Database\Factories\CollateralFactory> */
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'condition',
        'description',
        'value',
        'image_path',
        'documents',
        'registered_date',
        'loan_id',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    protected $casts = [
        'documents' => 'array',
        'registered_date' => 'date',
        'value' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        $disk = env('SUPABASE_URL') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->image_path);
    }
}
