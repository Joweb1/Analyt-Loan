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
        'organization_id',
        'borrower_id',
        'loan_officer_id',
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
        'penalty_value',
        'penalty_type',
        'penalty_frequency',
        'override_system_penalty',
        'description',
        'attachments',
        'status',
    ];

    protected $casts = [
        'release_date' => 'date',
        'attachments' => 'array',
        'override_system_penalty' => 'boolean',
        'penalty_value' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function repayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    public function scheduledRepayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ScheduledRepayment::class)->orderBy('due_date');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function collateral(): HasOne
    {
        return $this->hasOne(Collateral::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }
}
