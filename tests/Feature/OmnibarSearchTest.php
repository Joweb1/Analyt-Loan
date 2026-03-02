<?php

namespace Tests\Feature;

use App\Livewire\Components\OmnibarSearch;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OmnibarSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_search_borrower_by_custom_id()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
            'custom_id' => 'CUS-TESTID',
        ]);

        Livewire::actingAs($user)
            ->test(OmnibarSearch::class)
            ->set('query', 'CUS-TESTID')
            ->assertSet('query', 'CUS-TESTID')
            ->assertSee($borrowerUser->name);
    }

    public function test_it_can_search_borrower_by_custom_id_with_prefix()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
            'custom_id' => 'CUS-PREFIX',
        ]);

        Livewire::actingAs($user)
            ->test(OmnibarSearch::class)
            ->set('query', 'customer:CUS-PREFIX')
            ->assertSee($borrowerUser->name);
    }
}
