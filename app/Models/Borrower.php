<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $phone
 * @property int $trust_score
 * @property int $portal_access
 * @property string|null $photo_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $date_of_birth
 * @property string|null $gender
 * @property string|null $passport_photograph
 * @property string|null $biometric_data
 * @property string|null $national_identity_number
 * @property string|null $identity_document
 * @property array<array-key, mixed>|null $bank_account_details
 * @property string|null $bank_statement
 * @property array<array-key, mixed>|null $employment_information
 * @property string|null $income_proof
 * @property string|null $credit_score
 * @property string|null $marital_status
 * @property int|null $dependents
 * @property array<array-key, mixed>|null $next_of_kin_details
 * @property string $user_id
 * @property string|null $bvn
 * @property string|null $address
 * @property string|null $guarantor_id
 * @property string|null $organization_id
 * @property array<array-key, mixed>|null $custom_data
 * @property string $kyc_status
 * @property string|null $rejection_reason
 * @property int $onboarding_step
 * @property-read \App\Models\User|null $guarantor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Loan> $loans
 * @property-read int|null $loans_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\SavingsAccount|null $savingsAccount
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\BorrowerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereBankAccountDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereBankStatement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereBiometricData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereBvn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereCreditScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereCustomData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereDependents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereEmploymentInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereGuarantorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereIdentityDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereIncomeProof($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereKycStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereNationalIdentityNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereNextOfKinDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereOnboardingStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower wherePassportPhotograph($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower wherePhotoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower wherePortalAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereTrustScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Borrower extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected static function booted()
    {
        static::creating(function ($borrower) {
            if (empty($borrower->custom_id)) {
                $borrower->custom_id = 'CUS-'.strtoupper(\Illuminate\Support\Str::random(6));
            }
        });
    }

    protected $fillable = [
        'organization_id',
        'portfolio_id',
        'user_id',
        'custom_id',
        'guarantor_id',
        'external_guarantor_id',
        'phone',
        'bvn',
        'trust_score',
        'kyc_status',
        'rejection_reason',
        'portal_access',
        'photo_url',
        'date_of_birth',
        'gender',
        'address',
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
        'custom_data',
        'onboarding_step',
    ];

    protected $appends = [
        'photo_url',
        'passport_photograph_url',
        'identity_document_url',
        'bank_statement_url',
        'income_proof_url',
        'total_debt',
        'active_loans_count',
    ];

    protected $attributes = [
        'kyc_status' => 'pending',
    ];

    protected $casts = [
        'bank_account_details' => 'array',
        'employment_information' => 'array',
        'next_of_kin_details' => 'array',
        'custom_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guarantor_id');
    }

    public function externalGuarantor(): BelongsTo
    {
        return $this->belongsTo(Guarantor::class, 'external_guarantor_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function getTotalDebtAttribute(): float
    {
        return (float) $this->loans()->whereIn('status', ['active', 'overdue'])->sum('amount');
    }

    public function getActiveLoansCountAttribute(): int
    {
        return $this->loans()->whereIn('status', ['active', 'overdue'])->count();
    }

    public function savingsAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SavingsAccount::class);
    }

    public function recalculateTrustScore(): void
    {
        $this->trust_score = \App\Services\TrustScoringService::calculate($this);
        $this->save();
    }

    /**
     * URL Accessors
     */
    public function getPhotoUrlAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($value);
    }

    public function getPassportPhotographUrlAttribute(): ?string
    {
        if (! $this->passport_photograph) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->passport_photograph);
    }

    public function getIdentityDocumentUrlAttribute(): ?string
    {
        if (! $this->identity_document) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->identity_document);
    }

    public function getBankStatementUrlAttribute(): ?string
    {
        if (! $this->bank_statement) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->bank_statement);
    }

    public function getIncomeProofUrlAttribute(): ?string
    {
        if (! $this->income_proof) {
            return null;
        }
        $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->income_proof);
    }
}
