<?php

namespace Tests\Feature\Livewire\Borrower;

use App\Livewire\Borrower\Home;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_successfully_and_calculates_credit_limit_as_money()
    {
        $organization = Organization::factory()->create(['currency_code' => 'NGN']);
        $user = User::factory()->create(['organization_id' => $organization->id, 'type' => 'customer']);
        $borrower = Borrower::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'trust_score' => 50,
            'kyc_status' => 'approved',
            'onboarding_step' => 4,
        ]);

        Livewire::actingAs($user)
            ->test(Home::class)
            ->assertStatus(200)
            ->assertViewHas('creditLimit', function ($limit) {
                return $limit instanceof Money;
            })
            ->assertSee('₦');
    }
}
