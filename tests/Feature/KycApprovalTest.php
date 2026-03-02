<?php

namespace Tests\Feature;

use App\Livewire\Admin\KycApproval;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KycApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_can_approve_kyc()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->assignRole('Admin');

        $borrowerUser = User::factory()->create(['organization_id' => $organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $organization->id,
            'user_id' => $borrowerUser->id,
            'kyc_status' => 'pending',
        ]);

        Livewire::actingAs($user)
            ->test(KycApproval::class)
            ->assertSee($borrowerUser->name)
            ->call('approveKyc', $borrower->id);

        $this->assertEquals('approved', $borrower->fresh()->kyc_status);
    }
}
