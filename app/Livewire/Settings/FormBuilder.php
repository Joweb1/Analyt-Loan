<?php

namespace App\Livewire\Settings;

use App\Models\FormFieldConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class FormBuilder extends Component
{
    public $sections = [
        'identity' => 'Identity & Contact',
        'documents' => 'Identification Documents',
        'financial' => 'Financial & Employment',
        'family' => 'Family & Social',
        'guarantor' => 'Guarantor Selection',
    ];

    public $fieldTypes = [
        'text' => 'Text',
        'number' => 'Number',
        'email' => 'Email',
        'date' => 'Date',
        'select' => 'Dropdown',
        'file' => 'File Upload',
        'textarea' => 'Text Area',
    ];

    public $formTypes = [
        'borrower' => 'Borrower Form',
        'saver' => 'Saver Form',
        'guarantor' => 'Guarantor Form',
        'staff' => 'Staff Form',
    ];

    // Form Selection
    public $currentFormType = 'borrower';

    // New Field State
    public $newFieldSection = 'identity';

    public $newFieldLabel;

    public $newFieldType = 'text';

    public $newFieldOptions = ''; // Comma separated for simplicity in UI

    public function mount()
    {
        $this->ensureDefaultConfig();
    }

    public function updatedCurrentFormType()
    {
        $this->ensureDefaultConfig();
    }

    public function ensureDefaultConfig()
    {
        $orgId = Auth::user()->organization_id;
        self::seedDefaults($orgId, $this->currentFormType);
    }

    public static function seedDefaults($orgId, $type = 'borrower')
    {
        if (! $orgId) {
            return;
        }

        if (FormFieldConfig::where('organization_id', $orgId)->where('form_type', $type)->exists()) {
            return;
        }

        // Default Schema
        $borrowerDefaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => false],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['male', 'female', 'other'], 'required' => true],
                ['name' => 'marital_status', 'label' => 'Marital Status', 'type' => 'select', 'options' => ['Single', 'Married', 'Divorced', 'Widowed'], 'required' => true],
                ['name' => 'collection_group', 'label' => 'Collection Group', 'type' => 'select', 'options' => ['Monday Group', 'Tuesday Group', 'Wednesday Group', 'Thursday Group', 'Friday Group'], 'required' => false],
                ['name' => 'dependents', 'label' => 'Number of Dependents', 'type' => 'number', 'required' => true],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => true],
            ],
            'documents' => [
                ['name' => 'bvn', 'label' => 'BVN', 'type' => 'text', 'required' => true],
                ['name' => 'nin', 'label' => 'NIN', 'type' => 'text', 'required' => true],
                ['name' => 'passport_photo', 'label' => 'Passport Photograph', 'type' => 'file', 'required' => true],
                ['name' => 'identity_document', 'label' => 'Identity Document (ID Card)', 'type' => 'file', 'required' => true],
                ['name' => 'biometric_data', 'label' => 'Biometric Data (Optional)', 'type' => 'file', 'required' => false],
            ],
            'financial' => [
                ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
                ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                ['name' => 'bank_account_name', 'label' => 'Account Name', 'type' => 'text', 'required' => true],
                ['name' => 'bank_statement', 'label' => 'Bank Statement (Last 3 Months)', 'type' => 'file', 'required' => false],
                ['name' => 'is_employed', 'label' => 'Currently Employed?', 'type' => 'select', 'options' => ['Yes', 'No'], 'required' => true],
                ['name' => 'employer_name', 'label' => 'Employer Name', 'type' => 'text', 'required' => false],
                ['name' => 'job_title', 'label' => 'Job Title', 'type' => 'text', 'required' => false],
                ['name' => 'salary', 'label' => 'Monthly Income (₦)', 'type' => 'number', 'required' => false],
                ['name' => 'employer_address', 'label' => 'Employer Address', 'type' => 'textarea', 'required' => false],
                ['name' => 'income_proof', 'label' => 'Income Proof (Payslip)', 'type' => 'file', 'required' => false],
            ],
            'family' => [
                ['name' => 'next_of_kin_name', 'label' => 'Next of Kin Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'next_of_kin_relationship', 'label' => 'Relationship', 'type' => 'text', 'required' => true],
                ['name' => 'next_of_kin_phone', 'label' => 'Next of Kin Phone', 'type' => 'text', 'required' => true],
            ],
            'guarantor' => [
                ['name' => 'guarantor_id', 'label' => 'Select Guarantor', 'type' => 'select', 'required' => false],
            ],
        ];

        $saverDefaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => false],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => false],
            ],
            'financial' => [
                ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
                ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                ['name' => 'bank_account_name', 'label' => 'Account Name', 'type' => 'text', 'required' => true],
            ],
        ];

        $guarantorDefaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => false],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => false],
            ],
            'documents' => [
                ['name' => 'bvn', 'label' => 'BVN', 'type' => 'text', 'required' => false],
                ['name' => 'nin', 'label' => 'NIN', 'type' => 'text', 'required' => false],
            ],
        ];

        $staffDefaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'required' => false],
                ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['male', 'female', 'other'], 'required' => false],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => false],
            ],
        ];

        $defaults = $borrowerDefaults;
        if ($type === 'saver') {
            $defaults = $saverDefaults;
        } elseif ($type === 'guarantor') {
            $defaults = $guarantorDefaults;
        } elseif ($type === 'staff') {
            $defaults = $staffDefaults;
        }

        $order = 0;
        foreach ($defaults as $section => $fields) {
            foreach ($fields as $field) {
                FormFieldConfig::create([
                    'organization_id' => $orgId,
                    'form_type' => $type,
                    'section' => $section,
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => isset($field['options']) ? $field['options'] : null,
                    'is_required' => $field['required'],
                    'is_active' => true,
                    'is_system' => true,
                    'sort_order' => $order++,
                ]);
            }
        }
    }

    public function toggleRequired($id)
    {
        $field = FormFieldConfig::find($id);
        if ($field->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $field->is_required = ! $field->is_required;
        $field->save();
    }

    public function toggleActive($id)
    {
        $field = FormFieldConfig::find($id);
        if ($field->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $field->is_active = ! $field->is_active;
        $field->save();
    }

    public function addField($section = null)
    {
        if ($section) {
            $this->newFieldSection = $section;
        }

        $this->validate([
            'newFieldLabel' => 'required|string|max:255',
            'newFieldType' => 'required|in:'.implode(',', array_keys($this->fieldTypes)),
            'newFieldSection' => 'required|in:'.implode(',', array_keys($this->sections)),
        ]);

        $name = Str::slug($this->newFieldLabel, '_');
        $orgId = Auth::user()->organization_id;

        // Check unique name in section for current form type
        if (FormFieldConfig::where('organization_id', $orgId)->where('form_type', $this->currentFormType)->where('name', $name)->exists()) {
            $this->addError('newFieldLabel', 'A field with this name already exists.');

            return;
        }

        $options = null;
        if ($this->newFieldType === 'select' && $this->newFieldOptions) {
            $options = array_map('trim', explode(',', $this->newFieldOptions));
        }

        FormFieldConfig::create([
            'organization_id' => $orgId,
            'form_type' => $this->currentFormType,
            'section' => $this->newFieldSection,
            'name' => $name,
            'label' => $this->newFieldLabel,
            'type' => $this->newFieldType,
            'options' => $options,
            'is_required' => false,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 999, // Append to end
        ]);

        $this->reset(['newFieldLabel', 'newFieldType', 'newFieldOptions']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Field added successfully.']);
    }

    public function deleteField($id)
    {
        $field = FormFieldConfig::find($id);
        if ($field->organization_id !== Auth::user()->organization_id) {
            return;
        }
        if ($field->is_system) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'System fields cannot be deleted.']);

            return;
        }

        $field->delete();
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Field deleted.']);
    }

    public function render()
    {
        $configs = FormFieldConfig::where('organization_id', Auth::user()->organization_id)
            ->where('form_type', $this->currentFormType)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('livewire.settings.form-builder', [
            'configs' => $configs,
        ])->layout('layouts.app', ['title' => 'Form Builder']);
    }
}
