<?php

namespace Tests\Feature;

use App\Livewire\CollectionEntry;
use App\Livewire\CustomerList;
use App\Livewire\Vault;
use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_search_customers_by_name_case_insensitively()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        User::factory()->create([
            'organization_id' => $org->id,
            'type' => 'customer',
            'name' => 'John Doe',
        ]);
        User::factory()->create([
            'organization_id' => $org->id,
            'type' => 'customer',
            'name' => 'Jane Smith',
        ]);

        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->set('search', 'john')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function it_can_search_customers_by_borrower_id()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user = User::factory()->create([
            'organization_id' => $org->id,
            'type' => 'customer',
            'name' => 'John Doe',
        ]);
        Borrower::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'custom_id' => 'BR-777',
        ]);

        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->set('search', 'BR-777')
            ->assertSee('John Doe');
    }

    /** @test */
    public function it_can_search_loans_by_phone_in_collection_entry()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user1 = User::factory()->create(['organization_id' => $org->id, 'type' => 'customer', 'name' => 'Target', 'phone' => '123456789']);
        $borrower1 = Borrower::factory()->create(['user_id' => $user1->id, 'organization_id' => $org->id]);

        $user2 = User::factory()->create(['organization_id' => $org->id, 'type' => 'customer', 'name' => 'Other', 'phone' => '987654321']);
        $borrower2 = Borrower::factory()->create(['user_id' => $user2->id, 'organization_id' => $org->id]);

        Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower1->id,
            'loan_number' => 'LOAN-1',
            'status' => 'active',
        ]);
        Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower2->id,
            'loan_number' => 'LOAN-2',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(CollectionEntry::class)
            ->set('search', '123456789')
            ->assertSee('LOAN-1')
            ->assertDontSee('LOAN-2');
    }

    /** @test */
    public function it_resets_pagination_on_search()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        User::factory()->count(20)->create(['organization_id' => $org->id, 'type' => 'customer']);
        User::factory()->create(['organization_id' => $org->id, 'type' => 'customer', 'name' => 'Unique User']);

        // In Livewire 3 with WithPagination, the page is stored in $paginators['page'] usually,
        // or just use the gotoPage method.
        Livewire::actingAs($admin)
            ->test(CustomerList::class)
            ->call('gotoPage', 2)
            ->set('search', 'Unique')
            ->assertSee('Unique User');

        // Note: resetPage() is called internally, asserting status 200 and seeing the user is enough
        // because if it didn't reset, it would be on page 2 of 1 (empty).
    }

    /** @test */
    public function it_can_search_collateral_in_vault()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        Collateral::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Gold Watch',
            'status' => 'in_vault',
        ]);
        Collateral::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Silver Chain',
            'status' => 'in_vault',
        ]);

        Livewire::actingAs($admin)
            ->test(Vault::class)
            ->set('search', 'gold')
            ->assertSee('Gold Watch')
            ->assertDontSee('Silver Chain');
    }
}
