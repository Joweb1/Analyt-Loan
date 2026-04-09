<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CollateralForm;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CollateralFormTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);

        $this->loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_it_creates_collateral_linked_to_loan()
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->create('car.jpg', 100, 'image/jpeg');

        Livewire::actingAs($this->admin)
            ->test(CollateralForm::class)
            ->call('selectLoan', $this->loan->id)
            ->set('name', 'Toyota Camry')
            ->set('type', 'Vehicle')
            ->set('value', 2000000)
            ->set('condition', 'Good')
            ->set('image', $image)
            ->call('save')
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success';
            });

        $this->assertDatabaseHas('collaterals', [
            'organization_id' => $this->organization->id,
            'loan_id' => $this->loan->id,
            'name' => 'Toyota Camry',
            'value' => 200000000, // Minor units
        ]);
    }

    public function test_it_creates_branch_asset_collateral()
    {
        Livewire::actingAs($this->admin)
            ->test(CollateralForm::class)
            ->call('selectBranch')
            ->set('name', 'Office Generator')
            ->set('type', 'Equipment')
            ->set('value', 500000)
            ->set('condition', 'New')
            ->call('save')
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success';
            });

        $this->assertDatabaseHas('collaterals', [
            'organization_id' => $this->organization->id,
            'loan_id' => null,
            'name' => 'Office Generator',
            'value' => 50000000, // Minor units
        ]);
    }

    public function test_it_updates_existing_collateral()
    {
        $collateral = Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_id' => $this->loan->id,
            'value' => 1000000, // 1,000,000 Major = 100,000,000 Minor
        ]);

        Livewire::actingAs($this->admin)
            ->test(CollateralForm::class)
            ->call('selectLoan', $this->loan->id) // This loads the collateral
            ->set('value', 1500000)
            ->call('save')
            ->assertDispatched('custom-alert', function ($event, $params) {
                return $params[0]['type'] === 'success';
            });

        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
            'value' => 150000000, // Minor units
        ]);
    }
}
