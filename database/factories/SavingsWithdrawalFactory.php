<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\SavingsAccount;
use App\Models\SavingsWithdrawal;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SavingsWithdrawalFactory extends Factory
{
    protected $model = SavingsWithdrawal::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'savings_account_id' => SavingsAccount::factory(),
            'amount_withdrawn' => Money::fromMajor(500),
            'status' => 'pending',
            'transaction_date' => now(),
            'staff_id' => User::factory(),
            'reference' => 'W-'.strtoupper(Str::random(6)),
        ];
    }
}
