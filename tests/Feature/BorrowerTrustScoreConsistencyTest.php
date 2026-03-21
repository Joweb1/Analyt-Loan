<?php

namespace Tests\Feature;

use App\Livewire\BorrowerList;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BorrowerTrustScoreConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    #[Test]
    public function it_displays_consistent_trust_score_values_in_both_views()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        // Thresholds: >= 80 (Low), >= 50 (Medium), < 50 (High)
        $borrower = Borrower::factory()->create([
            'organization_id' => $org->id,
            'trust_score' => 85,
            'credit_score' => 300, // Different from trust score to detect usage of wrong field
        ]);

        // 1. Check Grid View (Default)
        Livewire::actingAs($admin)
            ->test(BorrowerList::class)
            ->set('viewMode', 'grid')
            ->assertSee('Low Risk')
            ->assertSee('85%')
            ->assertDontSee('High Risk') // 300 credit score would be High Risk if used
            ->assertDontSee('35%'); // 300 / 850 * 100 approx 35%

        // 2. Check List View
        Livewire::actingAs($admin)
            ->test(BorrowerList::class)
            ->set('viewMode', 'list')
            ->assertSee('Low Risk')
            ->assertSee('85%');
    }

    #[Test]
    public function it_uses_consistent_risk_labels_based_on_thresholds()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        // Test Medium Risk (50-79)
        $borrowerMedium = Borrower::factory()->create([
            'organization_id' => $org->id,
            'trust_score' => 60,
        ]);

        Livewire::actingAs($admin)
            ->test(BorrowerList::class)
            ->set('viewMode', 'grid')
            ->assertSee('Medium Risk')
            ->set('viewMode', 'list')
            ->assertSee('Medium Risk');

        // Test High Risk (< 50)
        $borrowerHigh = Borrower::factory()->create([
            'organization_id' => $org->id,
            'trust_score' => 30,
        ]);

        Livewire::actingAs($admin)
            ->test(BorrowerList::class)
            ->set('viewMode', 'grid')
            ->assertSee('High Risk')
            ->set('viewMode', 'list')
            ->assertSee('High Risk');
    }
}
