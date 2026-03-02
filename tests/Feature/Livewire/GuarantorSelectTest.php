<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Components\GuarantorSelect;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GuarantorSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_excludes_specified_user_id_from_results()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        // Create two borrowers
        $borrower1 = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => User::factory()->create(['organization_id' => $organization->id, 'name' => 'Searchable User'])->id,
        ]);

        $borrower2 = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => User::factory()->create(['organization_id' => $organization->id, 'name' => 'Excluded User'])->id,
        ]);

        $excludedUserId = $borrower2->user_id;

        Livewire::actingAs($user)
            ->test(GuarantorSelect::class, ['excludeId' => $excludedUserId])
            ->set('search', 'User')
            ->assertSee('Searchable User')
            ->assertDontSee('Excluded User');
    }
}
