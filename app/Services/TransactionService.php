<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Transaction;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Centralized method to record any financial transaction in the system.
     */
    public static function record(
        string $type,
        Money $amount,
        ?User $user = null,
        ?Model $related = null,
        ?string $paymentMethod = 'cash',
        ?string $notes = null,
        ?string $date = null,
        ?string $reference = null
    ): Transaction {
        $org = Organization::current();

        // Fallback: Resolve org from related model or user if global context is missing (common in tests)
        if (! $org) {
            $orgId = null;
            if ($related && isset($related->organization_id)) {
                $orgId = $related->organization_id;
            } elseif ($user && $user->organization_id) {
                $orgId = $user->organization_id;
            }

            if ($orgId) {
                $org = Organization::find($orgId);
            }
        }

        // If still no organization, we cannot record a transaction (FK constraint)
        if (! $org) {
            return new Transaction; // Return empty unsaved model for safety
        }

        $date = $date ?: $org->getSystemTime()->toDateString();
        $orgId = $org->id;

        if (! $reference) {
            $prefix = match ($type) {
                'registration_fee' => 'REG',
                'deposit' => 'DEP',
                'withdrawal' => 'WIT',
                'loan_disbursement' => 'DIS',
                'repayment' => 'REP',
                'daily_thrift' => 'THR',
                'penalty' => 'PEN',
                'interest' => 'INT',
                'charge' => 'CHG',
                'bonus' => 'BON',
                default => 'TRX'
            };
            $reference = $prefix.'-'.strtoupper(Str::random(8));
        }

        return Transaction::create([
            'organization_id' => $orgId,
            'user_id' => $user?->id,
            'performer_id' => Auth::id(),
            'type' => $type,
            'amount' => $amount,
            'currency_code' => $amount->getCurrency(),
            'reference' => $reference,
            'payment_method' => strtolower($paymentMethod ?? 'system'),
            'related_id' => $related?->getKey(),
            'related_type' => $related ? get_class($related) : null,
            'notes' => $notes,
            'transaction_date' => $date,
        ]);
    }
}
