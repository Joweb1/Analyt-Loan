<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'borrower_id',
        'amount',
        'loan_number',
        'loan_product',
        'release_date',
        'interest_rate',
        'interest_type',
        'duration',
        'duration_unit',
        'repayment_cycle',
        'num_repayments',
        'processing_fee',
        'processing_fee_type',
        'insurance_fee',
        'description',
        'attachments',
    ];

    protected $casts = [
        'release_date' => 'date',
        'attachments' => 'array',
    ];

    public function collateral(): HasOne
    {
        return $this->hasOne(Collateral::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
