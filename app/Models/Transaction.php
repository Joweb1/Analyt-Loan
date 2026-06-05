<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'user_id',
        'performer_id',
        'type',
        'amount',
        'currency_code',
        'reference',
        'payment_method',
        'related_id',
        'related_type',
        'notes',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::class,
            'transaction_date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
