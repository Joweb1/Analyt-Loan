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
        'name',
        'description',
        'value',
        'image_path',
        'loan_id',
        'status',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
