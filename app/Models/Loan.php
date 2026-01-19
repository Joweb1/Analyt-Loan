<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'borrower_id',
        'amount',
    ];

    public function collateral(): HasOne
    {
        return $this->hasOne(Collateral::class);
    }
}
