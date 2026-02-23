<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\FormFieldConfig;
use App\Models\Organization;
use App\Models\User;
use App\Traits\SterilizesPhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class BorrowerRegistrationForm extends Component
{
    use SterilizesPhone, WithFileUploads;

    // Organization
    public $organization_id;

    // System Fields
    public $name;

    public $email;

    public $phone;

    public $dob;

    public $gender;

    public $address;

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

    // Custom Fields Data
    public $customData = [];

    // Dynamic Configs
    public $configs = [];

    public function mount()
    {
        if (Auth::check() && Auth::user()->organization_id) {
            $this->organization_id = Auth::user()->organization_id;
        }

        $this->loadConfigs();
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

        // Check if config exists, if not maybe seed defaults (though FormBuilder logic usually handles seeding on visit,
        // here we might just get empty array if org hasn't set up forms yet.
        // Ideally, we should seed on org creation or first access)

        $rawConfigs = FormFieldConfig::where('organization_id', $this->organization_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // If no configs found, rely on default behavior (hardcoded fields) OR seed defaults on the fly?
        // Let's seed defaults on the fly to ensure consistency
        if ($rawConfigs->isEmpty()) {
            // Instantiate FormBuilder just to trigger default seeding?
            // Or just duplicate the logic. Duplicating logic is cleaner than instantiating a Livewire component.
            // For now, let's assume if empty, we fall back to standard hardcoded view logic?
            // Actually, the view needs to know if it should render dynamically or not.
            // Let's try to seed defaults if missing.
            $builder = new \App\Livewire\Settings\FormBuilder;
            // Mock auth for builder? No, builder relies on Auth::user()->organization_id.
            // Let's just create a static helper or service for seeding.
            // For now, I'll just check if rawConfigs is empty and if so, return empty configs
            // and the view will handle "if configs empty, show default form".
            $this->configs = [];
        } else {
            $this->configs = $rawConfigs->groupBy('section')->toArray();
        }
    }

    protected function getDynamicRules()
    {
        $rules = [
            'organization_id' => 'required|exists:organizations,id',
            'password' => 'required|string|confirmed|min:8',
        ];

        if (empty($this->configs)) {
            // Fallback to hardcoded rules if no config
            return array_merge($rules, [
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255',
                'phone' => 'required|string|max:255|unique:users,phone',
                'dob' => 'required|date',
                'gender' => 'required|string',
                'address' => 'required|string',
                'bvn' => 'required|string|size:11',
                'nin' => 'required|string|size:11',
                'passport_photo' => 'required|image|max:5120',
                'biometric_data' => 'nullable|file|max:10240',
                'identity_document' => 'required|file|max:10240',
                'bank_statement' => 'nullable|file|max:10240',
                'income_proof' => 'nullable|file|max:10240',
                'credit_score' => 'nullable|string',
                'marital_status' => 'required|string',
                'dependents' => 'required|integer',
                'bank_name' => 'required|string',
                'account_number' => 'required|string',
                'bank_account_name' => 'required|string',
                'employer_name' => 'nullable|string',
                'job_title' => 'nullable|string',
                'salary' => 'nullable|numeric',
                'employer_address' => 'nullable|string',
                'next_of_kin_name' => 'required|string',
                'next_of_kin_relationship' => 'required|string',
                'next_of_kin_phone' => 'required|string',
                'guarantor_id' => 'nullable|exists:users,id',
            ]);
        }

        foreach ($this->configs as $section => $fields) {
            foreach ($fields as $field) {
                $fieldName = $field['name'];
                $isSystem = $field['is_system'];
                $isRequired = $field['is_required'];
                $type = $field['type'];

                // Determine validation string
                $rule = [];
                if ($isRequired) {
                    $rule[] = 'required';
                } else {
                    $rule[] = 'nullable';
                }

                if ($type === 'email') {
                    $rule[] = 'email';
                }
                if ($type === 'number') {
                    $rule[] = 'numeric';
                }
                if ($type === 'date') {
                    $rule[] = 'date';
                }
                if ($type === 'file') {
                    $rule[] = 'file|max:10240';
                } // Generic max

                // Specific rules for identification
                if (in_array($fieldName, ['bvn', 'nin'])) {
                    $rule[] = 'string';
                    $rule[] = 'size:11';
                }

                // Map to property
                if ($isSystem) {
                    $rules[$fieldName] = $rule;
                } else {
                    $rules['customData.'.$fieldName] = $rule;
                }
            }
        }

        return $rules;
    }

    public function save()
    {
        $this->phone = $this->sterilize($this->phone);
        $this->next_of_kin_phone = $this->sterilize($this->next_of_kin_phone);

        // Check Org KYC Status
        $org = Organization::find($this->organization_id);
        if (! $org || $org->kyc_status !== 'approved') {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Registration is currently disabled for this organization (KYC Pending/Rejected).']);

            return;
        }

        try {
            // Update fallback rules for phone
            $rules = $this->getDynamicRules();
            if (isset($rules['phone'])) {
                $rules['phone'] = 'required|string|size:13|unique:users,phone';
            }
            if (isset($rules['next_of_kin_phone'])) {
                $rules['next_of_kin_phone'] = 'required|string|size:13';
            }

            $validatedData = $this->validate($rules);

            // User Creation/Update
            $user = User::where('phone', $this->phone)->first();
            if ($user) {
                if ($user->organization_id !== $this->organization_id) {
                    $this->addError('phone', 'User exists in another organization.');

                    return;
                }
                // Update basic user info
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                ]);
            } else {
                $user = User::create([
                    'organization_id' => $this->organization_id,
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($this->password),
                ]);
            }

            $borrowerRole = Role::findByName('Borrower');
            if (! $user->hasRole('Borrower')) {
                $user->assignRole($borrowerRole);
            }

            $borrower = Borrower::where('user_id', $user->id)->first() ?? new Borrower;
            $borrower->organization_id = $this->organization_id;
            $borrower->user_id = $user->id;
            $borrower->kyc_status = 'approved';

            $borrower->phone = $this->phone;
            $borrower->date_of_birth = $this->dob;
            $borrower->gender = $this->gender;
            $borrower->address = $this->address;
            $borrower->bvn = $this->bvn;
            $borrower->national_identity_number = $this->nin;
            $borrower->credit_score = $this->credit_score;
            $borrower->marital_status = $this->marital_status;
            $borrower->dependents = $this->dependents;
            $borrower->guarantor_id = $this->guarantor_id;

            // Computed Fields
            $borrower->bank_account_details = [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->bank_account_name,
            ];

            if ($this->is_employed) {
                $borrower->employment_information = [
                    'employer_name' => $this->employer_name,
                    'job_title' => $this->job_title,
                    'monthly_income' => $this->salary,
                    'employer_address' => $this->employer_address,
                    'employment_status' => 'Employed',
                ];
            } else {
                $borrower->employment_information = [
                    'employment_status' => 'Self-employed',
                ];
            }

            $borrower->next_of_kin_details = [
                'name' => $this->next_of_kin_name,
                'relationship' => $this->next_of_kin_relationship,
                'phone' => $this->next_of_kin_phone,
            ];

            // File Uploads
            if ($this->passport_photo) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->passport_photo->getClientOriginalExtension();
                $path = 'passport-photos/'.$filename;
                $stream = fopen($this->passport_photo->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->passport_photograph = $path;
            }
            if ($this->biometric_data) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->biometric_data->getClientOriginalExtension();
                $path = 'biometrics/'.$filename;
                $stream = fopen($this->biometric_data->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->biometric_data = $path;
            }
            if ($this->bank_statement) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->bank_statement->getClientOriginalExtension();
                $path = 'bank-statements/'.$filename;
                $stream = fopen($this->bank_statement->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->bank_statement = $path;
            }
            if ($this->identity_document) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->identity_document->getClientOriginalExtension();
                $path = 'identity-documents/'.$filename;
                $stream = fopen($this->identity_document->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->identity_document = $path;
            }
            if ($this->income_proof) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->income_proof->getClientOriginalExtension();
                $path = 'income-proofs/'.$filename;
                $stream = fopen($this->income_proof->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->income_proof = $path;
            }

            // Save Custom Data
            // Handle file uploads in custom data
            foreach ($this->customData as $key => $value) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $filename = \Illuminate\Support\Str::random(40).'.'.$value->getClientOriginalExtension();
                    $path = 'custom-files/'.$filename;
                    $stream = fopen($value->getRealPath(), 'r');
                    \Illuminate\Support\Facades\Storage::disk('supabase')->put($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $this->customData[$key] = $path;
                }
            }
            $borrower->custom_data = $this->customData;

            $borrower->save();

            $this->dispatch('custom-alert', [
                'type' => 'success',
                'message' => 'Borrower registered successfully.',
            ]);

            $this->reset(['name', 'email', 'phone', 'dob', 'gender', 'address', 'bvn', 'nin', 'passport_photo', 'biometric_data', 'identity_document', 'bank_statement', 'income_proof', 'credit_score', 'marital_status', 'dependents', 'password', 'password_confirmation', 'bank_name', 'account_number', 'bank_account_name', 'employer_name', 'job_title', 'salary', 'employer_address', 'next_of_kin_name', 'next_of_kin_relationship', 'next_of_kin_phone', 'guarantor_id', 'customData']);

        } catch (ValidationException $e) {
            $this->dispatch('custom-alert', [
                'type' => 'error',
                'message' => 'Please fill all required fields correctly.',
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('custom-alert', [
                'type' => 'error',
                'message' => 'An error occurred. '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $organizations = Organization::where('status', 'active')->where('kyc_status', 'approved')->get();

        $users = $this->organization_id
            ? User::where('organization_id', $this->organization_id)->get()
            : collect();

        return view('livewire.borrower-registration-form', [
            'users' => $users,
            'organizations' => $organizations,
        ]);
    }
}
