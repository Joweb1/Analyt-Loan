<?php

namespace Tests\Feature\Livewire;

use App\Livewire\DisbursementRegister;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DisbursementRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(DisbursementRegister::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_can_be_filtered()
    {
        Livewire::actingAs($this->admin)
            ->test(DisbursementRegister::class)
            ->set('search', 'Test')
            ->assertStatus(200);
    }

    #[Test]
    public function it_updates_installment_date()
    {
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'release_date' => now(),
        ]);

        $newDate = now()->addDays(30)->toDateString();

        Livewire::actingAs($this->admin)
            ->test(DisbursementRegister::class)
            ->call('updateInstallmentDate', $loan->id, $newDate)
            ->assertDispatched('notify');

        $this->assertEquals($newDate, $loan->refresh()->installment_date->toDateString());
    }

    #[Test]
    public function it_updates_register_notes()
    {
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'release_date' => now(),
        ]);

        $note = 'Special note for this loan';

        Livewire::actingAs($this->admin)
            ->test(DisbursementRegister::class)
            ->call('updateNote', $loan->id, $note)
            ->assertDispatched('notify');

        $this->assertEquals($note, $loan->refresh()->register_notes);
    }
}
