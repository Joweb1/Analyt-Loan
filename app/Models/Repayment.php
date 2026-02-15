<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repayment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'loan_id',
        'amount',
        'payment_method',
        'collected_by',
        'principal_amount',
        'interest_amount',
        'extra_amount',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
