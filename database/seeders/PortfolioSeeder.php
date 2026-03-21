<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        if (! $org) {
            return;
        }

        $portfolios = [
            [
                'name' => 'Retail Portfolio',
                'description' => 'Small individual personal loans.',
            ],
            [
                'name' => 'SME Portfolio',
                'description' => 'Business and growth loans for small enterprises.',
            ],
            [
                'name' => 'Emergency Portfolio',
                'description' => 'Fast-track emergency relief loans.',
            ],
        ];

        $staff = User::where('organization_id', $org->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'Borrower');
            })->get();

        foreach ($portfolios as $index => $pData) {
            $portfolio = Portfolio::create([
                'organization_id' => $org->id,
                'name' => $pData['name'],
                'description' => $pData['description'],
            ]);

            // Assign some staff
            if ($staff->isNotEmpty()) {
                $portfolio->staff()->attach($staff->random(min(2, $staff->count()))->pluck('id'));
            }

            // Assign some borrowers
            $borrowers = Borrower::where('organization_id', $org->id)
                ->whereNull('portfolio_id')
                ->take(5)
                ->get();

            foreach ($borrowers as $borrower) {
                $borrower->update(['portfolio_id' => $portfolio->id]);
                // Sync their loans
                $borrower->loans()->update(['portfolio_id' => $portfolio->id]);
            }
        }
    }
}
