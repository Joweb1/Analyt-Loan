<?php

namespace App\Livewire\Settings;

use App\Models\FormFieldConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuarantorFormBuilder extends Component
{
    public $sections = [
        'identity' => 'Identity & Contact',
        'documents' => 'Identification Documents',
        'financial' => 'Financial & Employment',
        'family' => 'Family & Social',
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
        self::seedDefaults($orgId);
    }

    public static function seedDefaults($orgId)
    {
        if (! $orgId) {
            return;
        }

        if (FormFieldConfig::where('organization_id', $orgId)->where('form_type', 'guarantor')->exists()) {
            return;
        }

        // Default Schema for Guarantor
        $defaults = [
            'identity' => [
                ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => false],
                ['name' => 'address', 'label' => 'Residential Address', 'type' => 'textarea', 'required' => true],
            ],
            'documents' => [
                ['name' => 'bvn', 'label' => 'BVN (11 Digits)', 'type' => 'text', 'required' => true],
                ['name' => 'nin', 'label' => 'NIN (11 Digits)', 'type' => 'text', 'required' => true],
            ],
            'financial' => [
                ['name' => 'employer', 'label' => 'Current Employer', 'type' => 'text', 'required' => false],
                ['name' => 'income', 'label' => 'Approx. Monthly Income', 'type' => 'number', 'required' => false],
            ],
        ];

        $order = 0;
        foreach ($defaults as $section => $fields) {
            foreach ($fields as $field) {
                FormFieldConfig::create([
                    'organization_id' => $orgId,
                    'form_type' => 'guarantor',
                    'section' => $section,
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => data_get($field, 'options'),
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

        $name = \Illuminate\Support\Str::slug($this->newFieldLabel, '_');
        $orgId = Auth::user()->organization_id;

        // Check unique name in section
        if (FormFieldConfig::where('organization_id', $orgId)->where('form_type', 'guarantor')->where('name', $name)->exists()) {
            $this->addError('newFieldLabel', 'A field with this name already exists.');

            return;
        }

        $options = null;
        if ($this->newFieldType === 'select' && $this->newFieldOptions) {
            $options = array_map('trim', explode(',', $this->newFieldOptions));
        }

        FormFieldConfig::create([
            'organization_id' => $orgId,
            'form_type' => 'guarantor',
            'section' => $this->newFieldSection,
            'name' => $name,
            'label' => $this->newFieldLabel,
            'type' => $this->newFieldType,
            'options' => $options,
            'is_required' => false,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 999,
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
            ->where('form_type', 'guarantor')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('livewire.settings.guarantor-form-builder', [
            'configs' => $configs,
        ])->layout('layouts.app', ['title' => 'Guarantor Form Builder']);
    }
}
