<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StatusBoardSeeder extends Seeder
{
    public function run(): void
    {
        $borrowerRole = Role::firstOrCreate(['name' => 'Borrower']);

        // Helper to create borrower
        $createBorrower = function ($name, $creditScore) use ($borrowerRole) {
            $user = User::factory()->create(['name' => $name]);
            $user->assignRole($borrowerRole);

            return Borrower::factory()->create([
                'user_id' => $user->id,
                'credit_score' => $creditScore,
            ]);
        };

        // 1. Pending Approvals (Status: applied)
        // High Risk
        $b1 = $createBorrower('Adewale Adebayo', 800);
        $l1 = Loan::factory()->create([
            'borrower_id' => $b1->id,
            'amount' => 2500000,
            'status' => 'applied',
            'updated_at' => now()->subHours(2),
        ]);
        Collateral::factory()->create(['loan_id' => $l1->id, 'name' => 'MacBook Pro M2 Max', 'value' => 3000000]);

        // Medium Risk
        $b2 = $createBorrower('Ngozi Obi', 650);
        $l2 = Loan::factory()->create([
            'borrower_id' => $b2->id,
            'amount' => 1200000,
            'status' => 'applied',
            'updated_at' => now()->subHours(5),
        ]);
        Collateral::factory()->create(['loan_id' => $l2->id, 'name' => 'iPhone 13 Pro Max', 'value' => 1500000]);

        // 2. Pending Verification (Status: verification_pending)
        $b3 = $createBorrower('Chinedu Okafor', 400); // High Risk
        // Simulate missing BVN by setting it to null if the factory doesn't
        $b3->update(['bvn' => null]);
        $l3 = Loan::factory()->create([
            'borrower_id' => $b3->id,
            'amount' => 8500000,
            'status' => 'verification_pending',
        ]);
        Collateral::factory()->create(['loan_id' => $l3->id, 'name' => 'Toyota Corolla 2018', 'value' => 10000000]);

        // 3. Approved (Status: approved, updated today)
        $b4 = $createBorrower('Approved User 1', 750);
        $l4 = Loan::factory()->create([
            'borrower_id' => $b4->id,
            'amount' => 500000,
            'status' => 'approved',
            'updated_at' => now(), // Today
        ]);
        Collateral::factory()->create(['loan_id' => $l4->id, 'name' => 'Gold Chain', 'value' => 600000]);

        // 4. Active (Status: active)
        $b5 = $createBorrower('Tunde Folayan', 700);
        $l5 = Loan::factory()->create([
            'borrower_id' => $b5->id,
            'amount' => 4200000,
            'status' => 'active',
            'description' => 'Shop Expansion Loan',
        ]);
        Collateral::factory()->create(['loan_id' => $l5->id, 'name' => 'Shop Lease', 'value' => 5000000]);
        // Add repayments to show progress
        // 9/12 paid
        $repaymentAmount = $l5->amount / 12; // Simplified
        Repayment::factory()->count(9)->create([
            'loan_id' => $l5->id,
            'amount' => $repaymentAmount,
            'paid_at' => now(),
        ]);

        // 5. Closed (Status: repaid)
        $b6 = $createBorrower('Amina Ibrahim', 900);
        $l6 = Loan::factory()->create([
            'borrower_id' => $b6->id,
            'amount' => 950000,
            'status' => 'repaid',
        ]);
        Collateral::factory()->create(['loan_id' => $l6->id, 'name' => 'Apple Watch Ultra', 'value' => 1000000]);
    }
}
