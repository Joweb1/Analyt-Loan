<?php

namespace App\Livewire\Settings;

use App\Models\FormFieldConfig;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FormBuilder extends Component
{
    public $sections = [
        'identity' => 'Identity & Contact',
        'documents' => 'Identification Documents',
        'financial' => 'Financial & Employment',
        'family' => 'Family & Social',
        'guarantor' => 'Guarantor',
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

    // New Field State
    public $newFieldSection = 'identity';
    public $newFieldLabel;
    public $newFieldType = 'text';
    public $newFieldOptions = ''; // Comma separated for simplicity in UI

    public function mount()
    {
        $this->ensureDefaultConfig();
    }

    public function ensureDefaultConfig()
    {
        $orgId = Auth::user()->organization_id;
        
        if (!$orgId) return; // Should handle this case (e.g. Super Admin or error)

        if (FormFieldConfig::where('organization_id', $orgId)->exists()) {
            return;
        }

        // Default Schema
        $defaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => false],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => true],
            ],
            'documents' => [
                ['name' => 'bvn', 'label' => 'BVN', 'type' => 'text', 'required' => true],
                ['name' => 'nin', 'label' => 'NIN', 'type' => 'text', 'required' => true],
                ['name' => 'passport_photo', 'label' => 'Passport Photograph', 'type' => 'file', 'required' => true],
                ['name' => 'identity_document', 'label' => 'ID Document', 'type' => 'file', 'required' => true],
                ['name' => 'biometric_data', 'label' => 'Biometric Data', 'type' => 'file', 'required' => false],
            ],
            'financial' => [
                ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
                ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                ['name' => 'bank_account_name', 'label' => 'Account Name', 'type' => 'text', 'required' => true],
                ['name' => 'bank_statement', 'label' => 'Bank Statement', 'type' => 'file', 'required' => false],
                ['name' => 'is_employed', 'label' => 'Employment Status', 'type' => 'checkbox', 'required' => true], // checkbox handled as boolean logic usually
                ['name' => 'employer_name', 'label' => 'Employer Name', 'type' => 'text', 'required' => false],
                ['name' => 'salary', 'label' => 'Salary', 'type' => 'number', 'required' => false],
                ['name' => 'income_proof', 'label' => 'Income Proof', 'type' => 'file', 'required' => false],
            ],
            'family' => [
                ['name' => 'marital_status', 'label' => 'Marital Status', 'type' => 'select', 'options' => ['Single', 'Married', 'Divorced', 'Widowed'], 'required' => true],
                ['name' => 'dependents', 'label' => 'Dependents', 'type' => 'number', 'required' => true],
                ['name' => 'next_of_kin_name', 'label' => 'Next of Kin Name', 'type' => 'text', 'required' => true],
                ['name' => 'next_of_kin_phone', 'label' => 'Next of Kin Phone', 'type' => 'text', 'required' => true],
            ],
            'guarantor' => [
                ['name' => 'guarantor_id', 'label' => 'Select Guarantor', 'type' => 'select', 'required' => false], // complex type
            ]
        ];

        $order = 0;
        foreach ($defaults as $section => $fields) {
            foreach ($fields as $field) {
                FormFieldConfig::create([
                    'organization_id' => $orgId,
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
        if ($field->organization_id !== Auth::user()->organization_id) return;
        
        $field->is_required = !$field->is_required;
        $field->save();
    }

    public function toggleActive($id)
    {
        $field = FormFieldConfig::find($id);
        if ($field->organization_id !== Auth::user()->organization_id) return;
        
        // Ensure system fields that are critical cannot be disabled if logic prevents it
        // For now allow toggling, but in render we might hide core logic if disabled which is tricky.
        // Better to allow toggling only optional system fields or custom fields.
        
        $field->is_active = !$field->is_active;
        $field->save();
    }

    public function addField($section = null)
    {
        if ($section) {
            $this->newFieldSection = $section;
        }

        $this->validate([
            'newFieldLabel' => 'required|string|max:255',
            'newFieldType' => 'required|in:' . implode(',', array_keys($this->fieldTypes)),
            'newFieldSection' => 'required|in:' . implode(',', array_keys($this->sections)),
        ]);

        $name = \Illuminate\Support\Str::slug($this->newFieldLabel, '_');
        $orgId = Auth::user()->organization_id;

        // Check unique name in section
        if (FormFieldConfig::where('organization_id', $orgId)->where('name', $name)->exists()) {
            $this->addError('newFieldLabel', 'A field with this name already exists.');
            return;
        }

        $options = null;
        if ($this->newFieldType === 'select' && $this->newFieldOptions) {
            $options = array_map('trim', explode(',', $this->newFieldOptions));
        }

        FormFieldConfig::create([
            'organization_id' => $orgId,
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
        if ($field->organization_id !== Auth::user()->organization_id) return;
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
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('livewire.settings.form-builder', [
            'configs' => $configs
        ])->layout('layouts.app');
    }
}
