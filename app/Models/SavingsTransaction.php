<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $savings_account_id
 * @property numeric $amount
 * @property string $type
 * @property string|null $reference
 * @property string|null $notes
 * @property string $staff_id
 * @property \Illuminate\Support\Carbon $transaction_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SavingsAccount $savingsAccount
 * @property-read \App\Models\User $staff
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereSavingsAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsTransaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SavingsTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'savings_account_id',
        'amount',
        'type',
        'reference',
        'notes',
        'staff_id',
        'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
