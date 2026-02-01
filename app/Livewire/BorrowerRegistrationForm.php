<?php

namespace App\Livewire;

use App\Models\User;

use App\Models\Borrower;

use Illuminate\Support\Facades\Hash;

use Livewire\Component;

use Livewire\WithFileUploads;

use Spatie\Permission\Models\Role;

use Illuminate\Validation\ValidationException;



class BorrowerRegistrationForm extends Component

{

    use WithFileUploads;



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



    // New properties for structured data

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



    // New property for employment status

    public $is_employed = true;



    // New property for guarantor

    public $guarantor_id;



    public function save()

    {

        try {

            $this->validate([

                'name' => 'required|string|max:255',

                'email' => 'required|string|email|max:255',

                'phone' => 'required|string|max:255',

                'dob' => 'required|date',

                'gender' => 'required|string',

                'address' => 'required|string',

                'bvn' => 'required|string|max:11',

                'nin' => 'required|string|max:255',

                'passport_photo' => 'required|image|max:5120', // 5MB Max

                'biometric_data' => 'nullable|file|max:10240', // 10MB Max

                'identity_document' => 'required|file|max:10240',

                'bank_statement' => 'nullable|file|max:10240', // Made optional

                'income_proof' => 'nullable|file|max:10240',

                'credit_score' => 'nullable|string',

                'marital_status' => 'required|string',

                'dependents' => 'required|integer',

                'password' => 'required|string|confirmed|min:8',



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



            $user = User::where('email', $this->email)->first();



            if ($user) {

                // User exists, check if they are already a borrower

                if ($user->borrower) {

                    $this->addError('email', 'The email has already been taken.');

                    return;

                }

            } else {

                // User does not exist, create a new one

                $user = User::create([

                    'name' => $this->name,

                    'email' => $this->email,

                    'password' => Hash::make($this->password),

                ]);

            }

    

            $borrowerRole = Role::findByName('Borrower');

            $user->assignRole($borrowerRole);

    

            $borrower = new Borrower();

            $borrower->user_id = $user->id;

            $borrower->guarantor_id = $this->guarantor_id;

            $borrower->phone = $this->phone;

            $borrower->date_of_birth = $this->dob;

            $borrower->gender = $this->gender;

            $borrower->address = $this->address;

            $borrower->bvn = $this->bvn;

            $borrower->national_identity_number = $this->nin;

            $borrower->credit_score = $this->credit_score;

            $borrower->marital_status = $this->marital_status;

            $borrower->dependents = $this->dependents;

            

            $borrower->bank_account_details = "Bank Name: {$this->bank_name}, Account Number: {$this->account_number}, Account Name: {$this->bank_account_name}";

            

            if ($this->is_employed) {

                $borrower->employment_information = "Employer: {$this->employer_name}, Job Title: {$this->job_title}, Salary: {$this->salary}, Address: {$this->employer_address}";

            } else {

                $borrower->employment_information = "Self-employed";

            }

            

            $borrower->next_of_kin_details = "Name: {$this->next_of_kin_name}, Relationship: {$this->next_of_kin_relationship}, Phone: {$this->next_of_kin_phone}";

            

            $borrower->passport_photograph = $this->passport_photo->store('passport-photos', 'public');

            if($this->biometric_data){

                $borrower->biometric_data = $this->biometric_data->store('biometrics', 'public');

            }

            if($this->bank_statement){

                $borrower->bank_statement = $this->bank_statement->store('bank-statements', 'public');

            }

            $borrower->identity_document = $this->identity_document->store('identity-documents', 'public');

            if ($this->income_proof) {

                $borrower->income_proof = $this->income_proof->store('income-proofs', 'public');

            }

    

            $borrower->save();

    

            $this->dispatch('custom-alert', [

                'type' => 'success',

                'message' => 'Borrower registered successfully.'

            ]);

    

            $this->reset();



        } catch (ValidationException $e) {

            $this->dispatch('custom-alert', [

                'type' => 'error',

                'message' => 'Please fill all required fields correctly.'

            ]);

            throw $e;

        } catch (\Throwable $e) {

            $this->dispatch('custom-alert', [

                'type' => 'error',

                'message' => 'An error occurred during registration. Please check the form and try again.'

            ]);

        }

    }



    public function render()

    {

        $users = User::all();

        return view('livewire.borrower-registration-form', [

            'users' => $users,

        ]);

    }

}
