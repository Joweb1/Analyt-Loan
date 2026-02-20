<?php

namespace Tests\Unit;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BelongsToOrganizationTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_automatically_sets_organization_id_on_creation()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($user);

        $borrower = Borrower::create([
            'user_id' => $user->id,
            'phone' => '1234567890',
            'kyc_status' => 'pending',
        ]);

        $this->assertEquals($organization->id, $borrower->organization_id);
    }

    public function test_it_filters_queries_by_organization_id()
    {
        $org1 = Organization::factory()->create();
        $user1 = User::factory()->create(['organization_id' => $org1->id]);

        $org2 = Organization::factory()->create();
        $user2 = User::factory()->create(['organization_id' => $org2->id]);

        // Create loans for both
        Loan::factory()->create(['organization_id' => $org1->id]);
        Loan::factory()->create(['organization_id' => $org2->id]);

        // Act as user1
        $this->actingAs($user1);
        $this->assertEquals(1, Loan::count());
        $this->assertEquals($org1->id, Loan::first()->organization_id);

        // Act as user2
        $this->actingAs($user2);
        $this->assertEquals(1, Loan::count());
        $this->assertEquals($org2->id, Loan::first()->organization_id);
    }

    public function test_app_owner_is_exempt_from_global_scope()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        Loan::factory()->create(['organization_id' => $org1->id]);
        Loan::factory()->create(['organization_id' => $org2->id]);

        $appOwner = User::factory()->create(['email' => 'admin@analyt.ng']);
        config(['app.owner' => 'admin@analyt.ng']);

        $this->actingAs($appOwner);

        // App owner should see everything
        $this->assertEquals(2, Loan::count());
    }
}
