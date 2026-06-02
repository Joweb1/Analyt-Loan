<?php

namespace Tests\Unit;

use App\Models\Borrower;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class KycAutomationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_defaults_to_pending_for_new_borrowers()
    {
        $borrower = Borrower::factory()->create();

        $this->assertEquals('pending', $borrower->kyc_status);
    }
}
