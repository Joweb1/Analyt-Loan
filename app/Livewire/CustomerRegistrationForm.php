<?php

namespace App\Livewire;

use App\Livewire\Settings\FormBuilder;
use App\Models\Borrower;
use App\Models\FormFieldConfig;
use App\Models\Guarantor;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\Saver;
use App\Models\User;
use App\Services\TransactionService;
use App\Traits\HandlesStorageDisk;
use App\Traits\SterilizesPhone;
use App\ValueObjects\Money;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class CustomerRegistrationForm extends Component
{
    use HandlesStorageDisk, SterilizesPhone, WithFileUploads;

    // Organization
    public $organization_id;

    public $portfolio_id;

    // System Fields
    public $name;

    public $email;

    public $phone;

    public $dob;

    public $gender;

    public $address;

    public $collection_group;

    public $is_daily_saver = false;

    public $daily_target_amount = 0;

    public $bvn;

    public $nin;

    public $passport_photo;

    public $biometric_data;

    public $identity_document;

    public $bank_statement;

    public $income_proof;

    public $credit_score;

    public $marital_status;

    public $dependents;

    public $password;

    public $password_confirmation;

    // Structured Data (System Fields components)
    public $bank_name;

    public $account_number;

    public $bank_account_name;

    public $employer_name;

    public $job_title;

    public $salary;

    public $employer_address;

    public $next_of_kin_name;

    public $next_of_kin_relationship;

    public $next_of_kin_phone;

    public $is_employed = true;

    public $guarantor_id;

    public $guarantor_type; // 'internal' or 'external'

    #[Url]
    public $registration_type = 'borrower';

    #[On('guarantorSelected')]
    public function updateGuarantor($guarantor)
    {
        if ($guarantor) {
            $this->guarantor_id = $guarantor['id'];
            $this->guarantor_type = $guarantor['type'];
        } else {
            $this->guarantor_id = null;
            $this->guarantor_type = null;
        }
    }

    // Custom Fields Data
    public $customData = [];

    public $availableRoles = [];

    public $configs = [];

    public $staff_role = 'Staff';

    public function mount()
    {
        $allowedTypes = ['borrower', 'saver', 'guarantor'];
        if (Auth::check() && Auth::user()->isAdmin()) {
            $allowedTypes[] = 'staff';
        }

        if (! in_array($this->registration_type, $allowedTypes)) {
            $this->registration_type = 'borrower';
        }

        if (Auth::check() && Auth::user()->organization_id) {
            $this->organization_id = Auth::user()->organization_id;
        }

        $this->is_employed = 'Yes';
        $this->loadConfigs();
        $this->loadRoles();
    }

    public function loadRoles()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            // Staff and non-Admin/non-Borrower roles
            $this->availableRoles = Role::whereNotIn('name', ['Borrower', 'Saver', 'Guarantor', 'Admin', 'App Owner', 'Owner'])
                ->pluck('name')
                ->toArray();

            if (empty($this->availableRoles)) {
                $this->availableRoles = ['Staff'];
            }
        }
    }

    public function updatedRegistrationType()
    {
        if ($this->registration_type === 'staff' && ! Auth::user()->isAdmin()) {
            $this->registration_type = 'borrower';
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Only administrators can register staff members.']);
        }

        $this->loadConfigs();
        $this->resetValidation();
    }

    public function updatedOrganizationId()
    {
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        if (! $this->organization_id) {
            $this->configs = [];

            return;
        }

        $rawConfigs = FormFieldConfig::where('organization_id', $this->organization_id)
            ->where('form_type', $this->registration_type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // If no configs found, seed defaults for the current type
        if ($rawConfigs->isEmpty()) {
            FormBuilder::seedDefaults($this->organization_id, $this->registration_type);
            $rawConfigs = FormFieldConfig::where('organization_id', $this->organization_id)
                ->where('form_type', $this->registration_type)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        $this->configs = $rawConfigs->groupBy('section')->toArray();
    }

    protected function getDynamicRules()
    {
        $rules = [
            'registration_type' => 'required|in:borrower,saver,guarantor,staff',
            'organization_id' => 'required|exists:organizations,id',
        ];

        // Password is only compulsory for borrowers, staff or if provided
        if (in_array($this->registration_type, ['borrower', 'staff'])) {
            $rules['password'] = 'required|string|confirmed|min:8';
        } else {
            $rules['password'] = 'nullable|string|confirmed|min:4';
        }

        // Shared minimal fields
        $sharedRules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'required|string|max:255',
        ];

        if ($this->registration_type === 'staff') {
            $sharedRules['email'] = 'required|string|email|max:255';
        }

        if (in_array($this->registration_type, ['saver', 'guarantor', 'staff'])) {
            return array_merge($rules, $sharedRules);
        }

        if (empty($this->configs)) {
            // Fallback to hardcoded rules if no config
            return array_merge($rules, $sharedRules, [
                'dob' => 'required|date',
                'gender' => 'required|string',
                'address' => 'required|string',
                'bvn' => 'required|string|size:11',
                'nin' => 'required|string|size:11',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'bank_account_name' => 'required|string',
                'next_of_kin_name' => 'required|string',
                'next_of_kin_relationship' => 'required|string',
                'next_of_kin_phone' => 'required|string',
            ]);
        }

        // Build from dynamic config
        foreach ($this->configs as $section => $fields) {
            foreach ($fields as $field) {
                $fieldName = $field['name'];
                $rule = $field['is_required'] ? ['required'] : ['nullable'];

                $rule[] = match ($field['type']) {
                    'number' => 'numeric',
                    'email' => 'email',
                    'date' => 'date',
                    'file' => null,
                    default => 'string',
                };
                $rule = array_filter($rule);

                // Specific system field rules
                if ($fieldName === 'bvn' || $fieldName === 'nin') {
                    $rule[] = 'size:11';
                }
                if ($fieldName === 'account_number') {
                    $rule[] = 'size:10';
                }

                // File validation
                $currentValue = $this->$fieldName;
                if ($currentValue instanceof UploadedFile) {
                    $rule[] = 'file';
                    if ($fieldName === 'passport_photo') {
                        $rule[] = 'image';
                    }
                }

                $rules[$fieldName] = $rule;
            }
        }

        return $rules;
    }

    public function save()
    {
        if ($this->registration_type === 'staff' && ! Auth::user()->isAdmin()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized operation. Only administrators can register staff.']);

            return;
        }

        // 1. Sterilization
        $this->name = trim($this->name);
        $this->email = $this->email ? strtolower(trim($this->email)) : null;
        $this->phone = preg_replace('/[^0-9]/', '', $this->phone);

        if ($this->next_of_kin_phone) {
            $this->next_of_kin_phone = preg_replace('/[^0-9]/', '', $this->next_of_kin_phone);
        }

        // Check Org KYC Status
        $org = Organization::find($this->organization_id);
        if (! $org || $org->kyc_status !== 'approved') {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Registration is currently disabled for this organization (KYC Pending/Rejected).']);

            return;
        }

        try {
            $rules = $this->getDynamicRules();
            $this->validate($rules);

            // User Creation/Update - Check by both phone and email WITHIN the organization
            $user = User::where('organization_id', $this->organization_id)
                ->where(function ($q) {
                    $q->where('phone', $this->phone);
                    if ($this->email) {
                        $q->orWhere('email', $this->email);
                    }
                })->first();

            $targetType = $this->registration_type === 'staff' ? 'staff' : 'customer';

            if ($user) {
                // Determine new type carefully
                $newType = $user->type;
                if (! in_array($user->type, ['admin', 'owner', 'app owner'])) {
                    $newType = $targetType;
                }

                // If user exists in same organization, we update them
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'type' => $newType,
                ]);
            } else {
                // Determine password
                $finalPassword = $this->password ?: $org->default_customer_password ?: 'password';

                $user = User::create([
                    'organization_id' => $this->organization_id,
                    'type' => $targetType,
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($finalPassword),
                ]);
            }

            // Role Management
            $protectedRoles = ['Admin', 'Owner', 'App Owner'];
            $currentRoles = $user->getRoleNames()->toArray();
            $preservedRoles = array_intersect($currentRoles, $protectedRoles);

            if ($this->registration_type === 'staff') {
                // For staff, we sync only the staff role, removing any customer roles
                $user->syncRoles(array_merge($preservedRoles, [$this->staff_role]));
            } else {
                // For customers (Borrower, Saver, Guarantor), we can have multiple
                // But we must remove any staff roles
                $customerRoles = ['Borrower', 'Saver', 'Guarantor'];
                $intendedRole = ucfirst($this->registration_type);

                $newRoles = array_intersect($currentRoles, $customerRoles);

                if (! in_array($intendedRole, $newRoles)) {
                    $newRoles[] = $intendedRole;
                }

                $user->syncRoles(array_merge($preservedRoles, $newRoles));
            }

            if ($this->registration_type === 'staff') {
                $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Staff member registered successfully.']);
                $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation']);
                $this->mount(); // Reset to default state

                return;
            }

            if ($this->registration_type === 'saver') {
                $saver = Saver::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'organization_id' => $this->organization_id,
                        'portfolio_id' => $this->portfolio_id,
                        'phone' => $this->phone,
                        'is_daily_saver' => $this->is_daily_saver,
                        'daily_target_amount' => $this->daily_target_amount,
                        'kyc_status' => 'approved',
                    ]
                );

                // Record Registration Fee (₦1,000)
                TransactionService::record(
                    type: 'registration_fee',
                    amount: new Money(100000, $org->currency_code ?? 'NGN'),
                    user: $user,
                    related: $saver,
                    paymentMethod: 'cash',
                    notes: 'Saver Registration Fee'
                );

                $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Saver registered successfully.']);
                $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation']);

                return;
            }

            if ($this->registration_type === 'guarantor') {
                $guarantor = Guarantor::firstOrCreate(
                    ['email' => $this->email, 'organization_id' => $this->organization_id],
                    [
                        'user_id' => $user->id,
                        'portfolio_id' => $this->portfolio_id,
                        'name' => $this->name,
                        'phone' => $this->phone,
                    ]
                );

                TransactionService::record(
                    type: 'registration_fee',
                    amount: new Money(100000, $org->currency_code ?? 'NGN'),
                    user: $user,
                    related: $guarantor,
                    paymentMethod: 'cash',
                    notes: 'Guarantor Registration Fee'
                );

                $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Guarantor registered successfully.']);
                $this->reset(['name', 'email', 'phone', 'password', 'password_confirmation', 'address']);

                return;
            }

            $borrower = Borrower::where('user_id', $user->id)->first() ?? new Borrower;
            $borrower->organization_id = $this->organization_id;
            $borrower->portfolio_id = $this->portfolio_id;
            $borrower->user_id = $user->id;
            $borrower->kyc_status = 'approved';
            $borrower->phone = $this->phone;
            $borrower->date_of_birth = $this->dob;
            $borrower->gender = $this->gender;
            $borrower->address = $this->address;
            $borrower->collection_group = $this->collection_group;
            $borrower->is_daily_saver = $this->is_daily_saver;
            $borrower->daily_target_amount = $this->daily_target_amount;
            $borrower->bvn = $this->bvn;
            $borrower->national_identity_number = $this->nin;
            $borrower->credit_score = $this->credit_score;
            $borrower->marital_status = $this->marital_status;
            $borrower->dependents = $this->dependents;
            $borrower->bank_account_details = [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->bank_account_name,
            ];
            $borrower->employment_information = [
                'is_employed' => $this->is_employed,
                'employer_name' => $this->employer_name,
                'job_title' => $this->job_title,
                'salary' => $this->salary,
                'employer_address' => $this->employer_address,
            ];
            $borrower->next_of_kin_details = [
                'name' => $this->next_of_kin_name,
                'relationship' => $this->next_of_kin_relationship,
                'phone' => $this->next_of_kin_phone,
            ];

            if ($this->guarantor_id) {
                if ($this->guarantor_type === 'internal') {
                    $borrower->guarantor_id = $this->guarantor_id;
                } else {
                    $borrower->external_guarantor_id = $this->guarantor_id;
                }
            }

            // Handle standard file uploads
            $disk = $this->getStorageDisk();

            if ($this->passport_photo) {
                $filename = Str::random(40).'.'.$this->passport_photo->getClientOriginalExtension();
                $path = 'passports/'.$filename;
                $stream = fopen($this->passport_photo->getRealPath(), 'r');
                Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->passport_photograph = $path;
            }
            if ($this->bank_statement) {
                $filename = Str::random(40).'.'.$this->bank_statement->getClientOriginalExtension();
                $path = 'bank-statements/'.$filename;
                $stream = fopen($this->bank_statement->getRealPath(), 'r');
                Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->bank_statement = $path;
            }
            if ($this->identity_document) {
                $filename = Str::random(40).'.'.$this->identity_document->getClientOriginalExtension();
                $path = 'identity-documents/'.$filename;
                $stream = fopen($this->identity_document->getRealPath(), 'r');
                Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->identity_document = $path;
            }
            if ($this->income_proof) {
                $filename = Str::random(40).'.'.$this->income_proof->getClientOriginalExtension();
                $path = 'income-proofs/'.$filename;
                $stream = fopen($this->income_proof->getRealPath(), 'r');
                Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->income_proof = $path;
            }

            // Save Custom Data
            foreach ($this->customData as $key => $value) {
                if ($value instanceof UploadedFile) {
                    $filename = Str::random(40).'.'.$value->getClientOriginalExtension();
                    $path = 'custom-files/'.$filename;
                    $stream = fopen($value->getRealPath(), 'r');
                    Storage::disk($disk)->put($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $this->customData[$key] = $path;
                }
            }
            $borrower->custom_data = $this->customData;

            $isNewBorrower = ! $borrower->exists;
            $borrower->save();

            if ($isNewBorrower) {
                // Record Registration Fee Transaction (1,000 Naira = 100,000 minor units)
                TransactionService::record(
                    type: 'registration_fee',
                    amount: new Money(100000, $org->currency_code ?? 'NGN'),
                    user: $user,
                    related: $borrower,
                    notes: 'Initial registration fee'
                );
            }

            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Borrower registered successfully.']);
            $this->reset(['name', 'email', 'phone', 'dob', 'gender', 'address', 'bvn', 'nin', 'passport_photo', 'biometric_data', 'identity_document', 'bank_statement', 'income_proof', 'credit_score', 'marital_status', 'dependents', 'password', 'password_confirmation', 'bank_name', 'account_number', 'bank_account_name', 'employer_name', 'job_title', 'salary', 'employer_address', 'next_of_kin_name', 'next_of_kin_relationship', 'next_of_kin_phone', 'guarantor_id', 'customData']);

        } catch (ValidationException $e) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Please fill all required fields correctly.']);
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'An error occurred. '.$e->getMessage()]);
        }
    }

    public function render()
    {
        $organizations = Organization::where('status', 'active')->where('kyc_status', 'approved')->get();

        $portfolios = $this->organization_id
            ? Portfolio::where('organization_id', $this->organization_id)->get()
            : collect();

        $users = $this->organization_id
            ? User::where('organization_id', $this->organization_id)->get()
            : collect();

        return view('livewire.customer-registration-form', [
            'users' => $users,
            'organizations' => $organizations,
            'portfolios' => $portfolios,
        ]);
    }
}
