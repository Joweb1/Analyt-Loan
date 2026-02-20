<?php

namespace Tests\Unit;

use App\Models\Borrower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KycAutomationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_defaults_to_pending_for_new_borrowers()
    {
        $borrower = Borrower::create([
            'phone' => '2348012345678',
            'user_id' => User::factory()->create()->id,
        ]);

        $this->assertEquals('pending', $borrower->kyc_status);
    }
}
