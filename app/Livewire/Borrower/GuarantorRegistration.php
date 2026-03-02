<?php

namespace App\Livewire\Borrower;

use App\Models\FormFieldConfig;
use App\Models\Guarantor;
use App\Traits\SterilizesPhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class GuarantorRegistration extends Component
{
    use SterilizesPhone, WithFileUploads;

    public $name;

    public $phone;

    public $email;

    public $address;

    public $bvn;

    public $nin;

    public $employer;

    public $income;

    public $customData = [];

    public $configs = [];

    public function mount()
    {
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        $orgId = Auth::user()->organization_id;

        $rawConfigs = FormFieldConfig::where('organization_id', $orgId)
            ->where('form_type', 'guarantor')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($rawConfigs->isEmpty()) {
            \App\Livewire\Settings\GuarantorFormBuilder::seedDefaults($orgId);
            $rawConfigs = FormFieldConfig::where('organization_id', $orgId)
                ->where('form_type', 'guarantor')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        $this->configs = $rawConfigs->groupBy('section')->toArray();
    }

    protected function getDynamicRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'bvn' => 'required|string|size:11',
            'nin' => 'required|string|size:11',
        ];

        foreach ($this->configs as $section => $fields) {
            foreach ($fields as $field) {
                $fieldName = $field['name'];
                $isSystem = $field['is_system'];
                $isRequired = $field['is_required'];
                $type = $field['type'];

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
                    $currentValue = $isSystem ? $this->{$fieldName} : ($this->customData[$fieldName] ?? null);
                    if ($isRequired && ! $currentValue) {
                        $rule[] = 'required';
                    } else {
                        $rule[] = 'nullable';
                    }

                    if ($currentValue instanceof \Illuminate\Http\UploadedFile) {
                        $rule[] = 'file';
                        $rule[] = 'max:10240';
                    }
                }

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

        try {
            $rules = $this->getDynamicRules();
            $this->validate($rules);

            $orgId = Auth::user()->organization_id;

            $guarantor = new Guarantor;
            $guarantor->organization_id = $orgId;
            $guarantor->name = $this->name;
            $guarantor->phone = $this->phone;
            $guarantor->email = $this->email;
            $guarantor->address = $this->address;
            $guarantor->bvn = $this->bvn;
            $guarantor->national_identity_number = $this->nin;
            $guarantor->employer = $this->employer;
            $guarantor->income = $this->income;

            // Handle file uploads in custom data
            $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');
            foreach ($this->customData as $key => $value) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $filename = Str::random(40).'.'.$value->getClientOriginalExtension();
                    $path = 'guarantor-files/'.$filename;
                    $stream = fopen($value->getRealPath(), 'r');
                    \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $this->customData[$key] = $path;
                }
            }
            $guarantor->custom_data = $this->customData;

            $guarantor->save();

            $this->dispatch('custom-alert', [
                'type' => 'success',
                'message' => 'Guarantor registered successfully.',
            ]);

            $this->reset(['name', 'phone', 'email', 'address', 'bvn', 'nin', 'customData']);

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
        return view('livewire.borrower.guarantor-registration')
            ->layout('layouts.app', ['title' => 'Register Guarantor']);
    }
}
