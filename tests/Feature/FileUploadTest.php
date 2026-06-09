<?php

namespace Tests\Feature;

use App\Jobs\ProcessLoanAttachment;
use App\Livewire\CustomerRegistrationForm;
use App\Livewire\LoanForm;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->organization = Organization::factory()->create(['kyc_status' => 'approved']);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    /**
     * Test that customer registration correctly uploads files to the public disk in testing environment.
     */
    public function test_customer_registration_uploads_to_public_disk()
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::fake('supabase');

        $passport = UploadedFile::fake()->create('passport.jpg', 100, 'image/jpeg');
        $idCard = UploadedFile::fake()->create('id_card.pdf', 100);

        Livewire::actingAs($this->admin)
            ->test(CustomerRegistrationForm::class)
            ->set('name', 'Test Customer')
            ->set('phone', '08012345678')
            ->set('dob', '1990-01-01')
            ->set('gender', 'male')
            ->set('address', '123 Test St')
            ->set('bvn', '12345678901')
            ->set('nin', '12345678901')
            ->set('bank_name', 'Test Bank')
            ->set('account_number', '0123456789')
            ->set('bank_account_name', 'Test Account')
            ->set('next_of_kin_name', 'NOK Name')
            ->set('next_of_kin_relationship', 'Sibling')
            ->set('next_of_kin_phone', '08087654321')
            ->set('marital_status', 'Single')
            ->set('dependents', 0)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->set('passport_photo', $passport)
            ->set('identity_document', $idCard)
            ->call('save')
            ->assertHasNoErrors();

        $borrower = Borrower::latest()->first();

        $this->assertNotNull($borrower->passport_photograph);
        $this->assertNotNull($borrower->identity_document);

        // Determine which disk it should have used
        $expectedDisk = (config('filesystems.disks.supabase.is_configured') && ! app()->environment('testing'))
            ? 'supabase'
            : (config('filesystems.default') === 'local' ? 'public' : config('filesystems.default'));

        // Verify files exist on the expected disk
        Storage::disk($expectedDisk)->assertExists($borrower->passport_photograph);
        Storage::disk($expectedDisk)->assertExists($borrower->identity_document);
    }

    /**
     * Test that loan attachments are processed via the background job.
     */
    public function test_loan_attachment_dispatches_job()
    {
        Queue::fake();
        Storage::fake('local'); // Temp storage

        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $attachment = UploadedFile::fake()->create('loan_agreement.pdf', 500);

        Livewire::actingAs($this->admin)
            ->test(LoanForm::class)
            ->set('borrowerId', $borrower->id)
            ->set('loan_number', 'LN-FILE-001')
            ->set('amount', 50000)
            ->set('loan_product', 'Test Product')
            ->set('interest_rate', 5)
            ->set('duration', 3)
            ->set('attachments', $attachment)
            ->call('saveLoan')
            ->assertHasNoErrors();

        // Verify the job was dispatched
        Queue::assertPushed(ProcessLoanAttachment::class);

        $loan = Loan::where('loan_number', 'LN-FILE-001')->first();
        $this->assertNotNull($loan);
    }

    /**
     * Test the actual job execution to ensure it moves file from local temp to public storage.
     */
    public function test_process_loan_attachment_job_moves_file_to_public()
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);

        // Create a temp file
        $tempPath = 'temp-attachments/test_file.pdf';
        Storage::disk('local')->put($tempPath, 'dummy content');

        // Execute the job
        $job = new ProcessLoanAttachment($loan, $tempPath, 'original_name.pdf');
        $job->handle();

        $loan->refresh();
        $this->assertNotEmpty($loan->attachments);
        $path = $loan->attachments[0];

        // Determine which disk it should have used
        $expectedDisk = config('filesystems.disks.supabase.is_configured')
            ? 'supabase'
            : (config('filesystems.default') === 'local' ? 'public' : config('filesystems.default'));

        // Verify it moved to the expected disk
        Storage::disk($expectedDisk)->assertExists($path);
        // Verify temp file is deleted
        Storage::disk('local')->assertMissing($tempPath);
    }
}
