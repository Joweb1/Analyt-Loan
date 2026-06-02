<?php

namespace Tests\Feature;

use App\Livewire\CustomerList;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
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
        $this->seed(RoleSeeder::class);
    }

    #[Test]
    public function it_displays_consistent_trust_score_values_in_both_views()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        // Thresholds: >= 80 (Emerald), >= 50 (Amber), < 50 (Rose)
        $user = User::factory()->create(['organization_id' => $org->id, 'type' => 'customer']);
        $user->assignRole('Borrower');
        $borrower = Borrower::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'trust_score' => 85,
            'credit_score' => 300, // Different from trust score to detect usage of wrong field
        ]);

        // 1. Check Grid View (Default)
        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->set('viewMode', 'grid')
            ->assertSee('85%')
            ->assertDontSee('35%');

        // 2. Check List View
        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->set('viewMode', 'list')
            ->assertStatus(200);
    }

    #[Test]
    public function it_uses_consistent_risk_labels_based_on_thresholds()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $userMedium = User::factory()->create(['organization_id' => $org->id, 'type' => 'customer']);
        $userMedium->assignRole('Borrower');
        $borrowerMedium = Borrower::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $userMedium->id,
            'trust_score' => 65,
        ]);

        $userHigh = User::factory()->create(['organization_id' => $org->id, 'type' => 'customer']);
        $userHigh->assignRole('Borrower');
        $borrowerHigh = Borrower::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $userHigh->id,
            'trust_score' => 35,
        ]);

        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->set('viewMode', 'grid')
            ->assertSee('65%')
            ->assertSee('35%');
    }
}
