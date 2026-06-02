<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CustomerList;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerListTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(CustomerList::class)
            ->assertStatus(200);
    }

    public function test_it_filters_borrowers_by_search()
    {
        $this->seed(RoleSeeder::class);

        $user1 = User::factory()->create(['name' => 'UniqueSearchableName', 'organization_id' => $this->organization->id, 'type' => 'customer']);
        $user1->assignRole('Borrower');
        $borrower1 = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $user1->id,
        ]);

        $user2 = User::factory()->create(['name' => 'OtherNonMatchingName', 'organization_id' => $this->organization->id, 'type' => 'customer']);
        $user2->assignRole('Borrower');
        $borrower2 = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $user2->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CustomerList::class)
            ->set('search', 'UniqueSearchable')
            ->assertSee('UniqueSearchableName')
            ->assertDontSee('OtherNonMatchingName');
    }

    public function test_it_toggles_view_mode()
    {
        Livewire::actingAs($this->admin)
            ->test(CustomerList::class)
            ->call('toggleView', 'list')
            ->assertSet('viewMode', 'list');
    }
}
