<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $loan_id
 * @property string $borrower_id
 * @property numeric $amount
 * @property string $payment_method
 * @property string $reference_code
 * @property string|null $receipt_path
 * @property string $status
 * @property string|null $admin_notes
 * @property \Illuminate\Support\Carbon $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Borrower $borrower
 * @property-read \App\Models\Loan $loan
 * @property-read \App\Models\Organization $organization
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereBorrowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereLoanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereReceiptPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereReferenceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentProof whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PaymentProof extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'loan_id',
        'borrower_id',
        'amount',
        'payment_method',
        'reference_code',
        'receipt_path',
        'status',
        'admin_notes',
        'paid_at',
    ];

    protected $appends = [
        'receipt_url',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        $disk = env('SUPABASE_URL') ? 'supabase' : config('filesystems.default');

        return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->receipt_path);
    }
}
