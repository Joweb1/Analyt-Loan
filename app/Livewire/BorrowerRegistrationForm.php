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
use Livewire\Attributes\On;
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

    public $guarantor_type; // 'internal' or 'external'

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

    // Dynamic Configs
    public $configs = [];

    public function mount()
    {
        if (Auth::check() && Auth::user()->organization_id) {
            $this->organization_id = Auth::user()->organization_id;
        }

        $this->is_employed = 'Yes';
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
            ->where('form_type', 'borrower')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // If no configs found, rely on default behavior (hardcoded fields) OR seed defaults on the fly?
        if ($rawConfigs->isEmpty()) {
            \App\Livewire\Settings\FormBuilder::seedDefaults($this->organization_id);
            $rawConfigs = FormFieldConfig::where('organization_id', $this->organization_id)
                ->where('form_type', 'borrower')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        $this->configs = $rawConfigs->groupBy('section')->toArray();
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
                'email' => 'nullable|string|email|max:255|unique:users,email',
                'phone' => 'required|string|max:255|unique:users,phone',
                'dob' => 'required|date',
                'gender' => 'required|string',
                'address' => 'required|string',
                'bvn' => 'required|string|size:11',
                'nin' => 'required|string|size:11',
                'passport_photo' => ($this->passport_photo instanceof \Illuminate\Http\UploadedFile) ? ['required', 'image', 'max:5120'] : ['nullable'],
                'biometric_data' => ($this->biometric_data instanceof \Illuminate\Http\UploadedFile) ? ['nullable', 'file', 'max:10240'] : ['nullable'],
                'identity_document' => ($this->identity_document instanceof \Illuminate\Http\UploadedFile) ? ['required', 'file', 'max:10240'] : ['nullable'],
                'bank_statement' => ($this->bank_statement instanceof \Illuminate\Http\UploadedFile) ? ['nullable', 'file', 'max:10240'] : ['nullable'],
                'income_proof' => ($this->income_proof instanceof \Illuminate\Http\UploadedFile) ? ['nullable', 'file', 'max:10240'] : ['nullable'],
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
                'guarantor_id' => 'nullable|string',
                'guarantor_type' => 'nullable|in:internal,external',
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
                    if ($fieldName === 'email' && $isSystem) {
                        $rule[] = 'unique:users,email';
                    }
                }
                if ($fieldName === 'phone' && $isSystem) {
                    $rule[] = 'unique:users,phone';
                }
                if ($type === 'number') {
                    $rule[] = 'numeric';
                }
                if ($type === 'date') {
                    $rule[] = 'date';
                }
                if ($type === 'file') {
                    // If we already have a path (string) and it's required, we don't need 'required' rule again
                    // because the file has been uploaded to the temp storage or final storage.
                    // However, Livewire properties for files usually hold the UploadedFile object until saved.
                    // The issue is likely that when the form re-validates, it doesn't see the file object anymore or it's not a 'file' type.
                    $currentValue = $isSystem ? $this->{$fieldName} : ($this->customData[$fieldName] ?? null);

                    if ($isRequired && ! $currentValue) {
                        $rule[] = 'required';
                    } else {
                        $rule[] = 'nullable';
                    }

                    if ($currentValue instanceof \Illuminate\Http\UploadedFile) {
                        $rule[] = 'file';
                        if ($fieldName === 'passport_photo') {
                            $rule[] = 'image';
                        }
                        $rule[] = 'max:10240';
                    }
                }
                // Generic max

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
            if (isset($rules['email'])) {
                $rules['email'] = 'nullable|string|email|max:255|unique:users,email';
            }
            if (isset($rules['next_of_kin_phone'])) {
                $rules['next_of_kin_phone'] = 'required|string|size:13';
            }

            $validatedData = $this->validate($rules);

            // User Creation/Update - Check by both phone and email
            $user = User::where('phone', $this->phone)
                ->orWhere('email', $this->email)
                ->first();

            if ($user) {
                if ($user->organization_id !== $this->organization_id) {
                    $errorMessage = $user->phone === $this->phone 
                        ? 'User with this phone exists in another organization.' 
                        : 'User with this email exists in another organization.';
                    
                    $this->addError($user->phone === $this->phone ? 'phone' : 'email', $errorMessage);

                    return;
                }
                
                // If user exists with same phone/email, we update them
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
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
            $borrower->gender = strtolower((string) $this->gender);
            $borrower->address = $this->address;
            $borrower->bvn = $this->bvn;
            $borrower->national_identity_number = $this->nin;
            $borrower->credit_score = $this->credit_score;
            $borrower->marital_status = $this->marital_status;
            $borrower->dependents = $this->dependents;

            if ($this->guarantor_type === 'external') {
                $borrower->external_guarantor_id = $this->guarantor_id;
                $borrower->guarantor_id = null;
            } else {
                $borrower->guarantor_id = $this->guarantor_id;
                $borrower->external_guarantor_id = null;
            }

            // Computed Fields
            $borrower->bank_account_details = [
                'bank_name' => $this->bank_name,
                'account_number' => $this->account_number,
                'account_name' => $this->bank_account_name,
            ];

            if ($this->is_employed === 'Yes' || $this->is_employed === true || $this->is_employed === 1 || $this->is_employed === '1') {
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
            $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');
            if ($this->passport_photo) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->passport_photo->getClientOriginalExtension();
                $path = 'passport-photos/'.$filename;
                $stream = fopen($this->passport_photo->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->passport_photograph = $path;
            }
            if ($this->biometric_data) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->biometric_data->getClientOriginalExtension();
                $path = 'biometrics/'.$filename;
                $stream = fopen($this->biometric_data->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->biometric_data = $path;
            }
            if ($this->bank_statement) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->bank_statement->getClientOriginalExtension();
                $path = 'bank-statements/'.$filename;
                $stream = fopen($this->bank_statement->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->bank_statement = $path;
            }
            if ($this->identity_document) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->identity_document->getClientOriginalExtension();
                $path = 'identity-documents/'.$filename;
                $stream = fopen($this->identity_document->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $borrower->identity_document = $path;
            }
            if ($this->income_proof) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$this->income_proof->getClientOriginalExtension();
                $path = 'income-proofs/'.$filename;
                $stream = fopen($this->income_proof->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
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
                    \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
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
