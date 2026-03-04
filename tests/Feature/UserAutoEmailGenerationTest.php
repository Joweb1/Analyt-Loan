<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAutoEmailGenerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if the email field is automatically generated if missing.
     */
    public function test_email_field_is_automatically_generated_if_missing()
    {
        // Attempt to create a user without an email
        $user = User::create([
            'name' => 'Test User No Email',
            'phone' => '2348000000001',
            'password' => Hash::make('password'),
        ]);

        $this->assertNotNull($user->email);
        $this->assertStringContainsString('2348000000001@analyt-loan.com', $user->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Test if the email field is NOT overwritten if provided.
     */
    public function test_email_field_is_not_overwritten_if_provided()
    {
        $user = User::create([
            'name' => 'Test User With Email',
            'email' => 'custom@example.com',
            'phone' => '2348000000002',
            'password' => Hash::make('password'),
        ]);

        $this->assertEquals('custom@example.com', $user->email);
    }
}
