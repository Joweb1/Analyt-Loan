<?php

namespace Tests\Feature\Livewire;

use App\Livewire\BorrowerList;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BorrowerListTest extends TestCase
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
            ->test(BorrowerList::class)
            ->assertStatus(200);
    }

    public function test_it_filters_borrowers_by_search()
    {
        $borrower1 = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => User::factory()->create(['name' => 'UniqueSearchableName'])->id,
        ]);

        $borrower2 = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => User::factory()->create(['name' => 'OtherNonMatchingName'])->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(BorrowerList::class)
            ->set('search', 'UniqueSearchable')
            ->assertSee('UniqueSearchableName')
            ->assertDontSee('OtherNonMatchingName');
    }

    public function test_it_toggles_view_mode()
    {
        Livewire::actingAs($this->admin)
            ->test(BorrowerList::class)
            ->call('toggleView', 'list')
            ->assertSet('viewMode', 'list');
    }
}
