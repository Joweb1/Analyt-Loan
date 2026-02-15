<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collateral extends Model
{
    /** @use HasFactory<\Database\Factories\CollateralFactory> */
    use HasFactory, HasUuids;

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

    protected $casts = [
        'documents' => 'array',
        'registered_date' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
