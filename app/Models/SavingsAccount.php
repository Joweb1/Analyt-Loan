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
 * @property string $borrower_id
 * @property string $organization_id
 * @property string $account_number
 * @property numeric $balance
 * @property numeric $interest_rate
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Borrower $borrower
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SavingsTransaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Database\Factories\SavingsAccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereBorrowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SavingsAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SavingsAccount extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'borrower_id',
        'organization_id',
        'account_number',
        'balance',
        'interest_rate',
        'status',
    ];

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }
}
