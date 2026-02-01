<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrower extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'guarantor_id',
        'phone',
        'bvn',
        'trust_score',
        'portal_access',
        'photo_url',
        'date_of_birth',
        'gender',
        'passport_photograph',
        'biometric_data',
        'national_identity_number',
        'identity_document',
        'bank_account_details',
        'bank_statement',
        'employment_information',
        'income_proof',
        'credit_score',
        'marital_status',
        'dependents',
        'next_of_kin_details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guarantor_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}

