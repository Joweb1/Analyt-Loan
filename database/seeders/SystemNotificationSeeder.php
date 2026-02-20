<?php

namespace Database\Seeders;

use App\Models\Loan;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $loan = Loan::first();

        $notifications = [
            [
                'title' => 'New Loan Application',
                'message' => 'A new loan of ₦2,500,000 was applied for by Adewale Adebayo',
                'type' => 'success',
                'category' => 'loan',
                'created_at' => now()->subMinutes(15),
            ],
            [
                'title' => 'Loan Approved',
                'message' => 'Loan #LN-88219 for Tunde Folayan has been approved by Admin',
                'type' => 'success',
                'category' => 'loan',
                'created_at' => now()->subHours(2),
            ],
            [
                'title' => 'New Customer Registered',
                'message' => 'Customer Chinelo Okoro has been successfully registered in the system.',
                'type' => 'info',
                'category' => 'borrower',
                'created_at' => now()->subHours(5),
            ],
            [
                'title' => 'Loan Overdue Alert',
                'message' => 'Loan #LN-90124 for Musa Ibrahim is now 14 days OVERDUE',
                'type' => 'danger',
                'category' => 'loan',
                'created_at' => now()->subDay(),
            ],
            [
                'title' => 'KYC Verified',
                'message' => 'BVN verification successful for customer Amina Ibrahim',
                'type' => 'success',
                'category' => 'borrower',
                'created_at' => now()->subDays(2),
            ],
            [
                'title' => 'New Collateral Added',
                'message' => "Collateral 'MacBook Pro M2' has been added to Loan #LN-77610.",
                'type' => 'info',
                'category' => 'collateral',
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($notifications as $n) {
            SystemNotification::create(array_merge($n, [
                'user_id' => $admin?->id,
                'subject_id' => $loan?->id,
                'subject_type' => $loan ? get_class($loan) : null,
            ]));
        }
    }
}
