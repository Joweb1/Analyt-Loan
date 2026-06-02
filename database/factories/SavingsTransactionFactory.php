<?php

namespace Database\Factories;

use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsTransactionFactory extends Factory
{
    protected $model = SavingsTransaction::class;

    public function definition(): array
    {
        return [
            'savings_account_id' => SavingsAccount::factory(),
            'amount' => Money::fromMajor(100),
            'type' => 'deposit',
            'transaction_date' => now(),
            'staff_id' => User::factory(),
        ];
    }
}
