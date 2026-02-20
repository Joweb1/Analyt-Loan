<?php

namespace Tests\Feature\Livewire;

use App\Livewire\OrgRegistrationForm;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrgRegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Admin']);
    }

    public function test_it_renders_successfully()
    {
        Livewire::test(OrgRegistrationForm::class)
            ->assertStatus(200);
    }

    public function test_it_registers_an_organization_and_admin()
    {
        Storage::fake('public');
        $logo = UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');

        Livewire::test(OrgRegistrationForm::class)
            ->set('orgName', 'Acme Lending')
            ->set('orgEmail', 'info@acme.com')
            ->set('orgLogo', $logo)
            ->set('adminName', 'Admin User')
            ->set('phone', '08012345678')
            ->set('email', 'admin@acme.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('organizations', [
            'name' => 'Acme Lending',
            'email' => 'info@acme.com',
            'status' => 'active',
            'kyc_status' => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'phone' => '2348012345678', // Sterilized
        ]);

        $user = User::where('email', 'admin@acme.com')->first();
        $org = Organization::where('name', 'Acme Lending')->first();

        $this->assertEquals($org->id, $user->organization_id);
        $this->assertEquals($user->id, $org->owner_id);
        $this->assertTrue($user->hasRole('Admin'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_it_validates_required_fields()
    {
        Livewire::test(OrgRegistrationForm::class)
            ->call('save')
            ->assertHasErrors(['orgName', 'orgEmail', 'adminName', 'phone', 'password']);
    }
}
