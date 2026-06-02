<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Contracts\StorageProvider;
use App\Services\CircuitBreaker;
use App\Services\TrustScoringService;
use App\Traits\BelongsToOrganization;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $phone
 * @property string|null $collection_group
 * @property int $trust_score
 * @property Money $total_debt
 * @property int $active_loans_count
 * @property int $portal_access
 * @property string|null $photo_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @property bool $is_daily_saver
 * @property Money $daily_target_amount
 * @property string|null $guarantor_id
 * @property string|null $organization_id
 * @property array<array-key, mixed>|null $custom_data
 * @property string $kyc_status
 * @property string|null $rejection_reason
 * @property int $onboarding_step
 * @property-read User|null $guarantor
 * @property-read Collection<int, Loan> $loans
 * @property-read Collection<int, Repayment> $repayments
 * @property-read int|null $loans_count
 * @property-read Organization|null $organization
 * @property-read SavingsAccount|null $savingsAccount
 * @property-read User $user
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
 * @property string|null $custom_id
 * @property string|null $external_guarantor_id
 * @property string|null $portfolio_id
 * @property-read Guarantor|null $externalGuarantor
 * @property-read string|null $bank_statement_url
 * @property-read string|null $identity_document_url
 * @property-read string|null $income_proof_url
 * @property-read string|null $passport_photograph_url
 * @property-read Portfolio|null $portfolio
 * @property-read int|null $repayments_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereActiveLoansCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereCollectionGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereCustomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereDailyTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereExternalGuarantorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereIsDailySaver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower wherePortfolioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Borrower whereTotalDebt($value)
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
                $borrower->custom_id = 'CUS-'.strtoupper(Str::random(6));
            }
        });
    }

    protected $fillable = [
        'organization_id',
        'portfolio_id',
        'user_id',
        'custom_id',
        'collection_group',
        'is_daily_saver',
        'daily_target_amount',
        'guarantor_id',
        'external_guarantor_id',
        'phone',
        'bvn',
        'trust_score',
        'total_debt',
        'active_loans_count',
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
        'active_loans_count' => 'integer',
        'is_daily_saver' => 'boolean',
        'daily_target_amount' => MoneyCast::class,
    ];

    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = $value ? strtolower($value) : null;
    }

    public function setKycStatusAttribute($value)
    {
        $this->attributes['kyc_status'] = $value ? strtolower($value) : 'pending';
    }

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

    /** @return HasMany<Loan, $this> */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /** @return HasMany<Repayment, $this> */
    public function repayments(): HasMany
    {
        return $this->hasMany(Repayment::class);
    }

    /** @return HasOne<SavingsAccount, $this> */
    public function savingsAccount(): HasOne
    {
        return $this->hasOne(SavingsAccount::class, 'user_id', 'user_id');
    }

    public function getTotalDebtAttribute(): Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        $loans = $this->loans()->whereIn('status', ['active', 'defaulted', 'overdue'])->get();

        $totalOwedMinor = 0;
        foreach ($loans as $loan) {
            /** @var Loan $loan */
            $totalOwedMinor += $loan->balance->getMinorAmount();
        }

        return new Money($totalOwedMinor, $currency);
    }

    public function getActiveLoansCountAttribute(): int
    {
        return $this->loans()->where('status', 'active')->count();
    }

    public function recalculateTrustScore(): void
    {
        $this->trust_score = TrustScoringService::calculate($this);
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

        return CircuitBreaker::run('storage', function () use ($value) {
            return app(StorageProvider::class)->url($value);
        }, 'https://via.placeholder.com/150?text=Service+Unavailable');
    }

    public function getPassportPhotographUrlAttribute(): ?string
    {
        if (! $this->passport_photograph) {
            return null;
        }

        return CircuitBreaker::run('storage', function () {
            return app(StorageProvider::class)->url($this->passport_photograph);
        }, 'https://via.placeholder.com/150?text=Service+Unavailable');
    }

    public function getIdentityDocumentUrlAttribute(): ?string
    {
        if (! $this->identity_document) {
            return null;
        }

        return CircuitBreaker::run('storage', function () {
            return app(StorageProvider::class)->url($this->identity_document);
        }, null);
    }

    public function getBankStatementUrlAttribute(): ?string
    {
        if (! $this->bank_statement) {
            return null;
        }

        return CircuitBreaker::run('storage', function () {
            return app(StorageProvider::class)->url($this->bank_statement);
        }, null);
    }

    public function getIncomeProofUrlAttribute(): ?string
    {
        if (! $this->income_proof) {
            return null;
        }

        return CircuitBreaker::run('storage', function () {
            return app(StorageProvider::class)->url($this->income_proof);
        }, null);
    }
}
