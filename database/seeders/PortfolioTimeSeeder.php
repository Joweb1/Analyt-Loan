<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PortfolioTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $org = Organization::first();
        if (! $org) {
            return;
        }

        // 1. Seed Portfolios
        $portfolios = [
            [
                'name' => 'High-Growth SME',
                'description' => 'Portfolios focused on fast-scaling small businesses.',
            ],
            [
                'name' => 'Micro-Personal',
                'description' => 'Individual personal loans for daily needs.',
            ],
            [
                'name' => 'Agricultural Relief',
                'description' => 'Seasonal loans for rural farmers.',
            ],
        ];

        foreach ($portfolios as $pData) {
            $portfolio = Portfolio::updateOrCreate(
                ['organization_id' => $org->id, 'name' => $pData['name']],
                ['description' => $pData['description']]
            );

            // Assign random staff to this portfolio
            $staff = User::where('organization_id', $org->id)
                ->whereHas('roles', function ($q) {
                    $q->whereNotIn('name', ['Borrower']);
                })
                ->inRandomOrder()
                ->take(2)
                ->get();

            $portfolio->staff()->syncWithoutDetaching($staff->pluck('id'));

            // Assign unassigned borrowers to this portfolio
            $borrowers = Borrower::where('organization_id', $org->id)
                ->whereNull('portfolio_id')
                ->take(3)
                ->get();

            foreach ($borrowers as $borrower) {
                $borrower->update(['portfolio_id' => $portfolio->id]);
                // Sync their loans
                $borrower->loans()->update(['portfolio_id' => $portfolio->id]);
            }
        }

        // 2. Seed Time Management Settings
        // We'll set the organization to operate 2 days in the future to demonstrate the feature
        $org->update([
            'use_manual_date' => true,
            'operating_date' => Carbon::now()->addDays(2)->startOfDay(),
        ]);
    }
}
