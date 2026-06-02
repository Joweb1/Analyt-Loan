<?php

namespace App\Livewire\Components;

use App\Models\Guarantor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuarantorSelect extends Component
{
    public $search = '';

    public $results = [];

    public $selectedGuarantor = null; // ['id' => id, 'type' => 'user' | 'external', 'name' => name]

    public $excludeId = null;

    public $eventName = 'guarantorSelected';

    public function mount($eventName = 'guarantorSelected', $defaultGuarantor = null, $excludeId = null)
    {
        $this->eventName = $eventName;
        $this->excludeId = $excludeId;
        if ($defaultGuarantor) {
            $this->selectedGuarantor = $defaultGuarantor;
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];

            return;
        }

        $orgId = Auth::user()->organization_id;
        $term = '%'.strtolower(trim($this->search)).'%';

        // Search All Customers (Borrowers, Savers, Guarantor-Users)
        $this->results = User::where('type', 'customer')
            ->where('organization_id', $orgId)
            ->when($this->excludeId, function ($q) {
                $q->where('id', '!=', $this->excludeId);
            })
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                    ->orWhere('phone', 'like', $term);
            })
            ->take(10)
            ->get()
            ->map(function ($u) {
                $role = $u->getRoleNames()->first() ?? 'Customer';

                return [
                    'id' => $u->id,
                    'type' => 'internal',
                    'name' => $u->name,
                    'subtitle' => $role.' | '.$u->phone,
                    'custom_id' => $u->borrower->custom_id ?? ($u->guarantor->custom_id ?? ($u->saver->custom_id ?? 'CUST-'.substr($u->id, 0, 5))),
                ];
            })
            ->toArray();
    }

    public function selectGuarantor($id, $type, $name)
    {
        $this->selectedGuarantor = [
            'id' => $id,
            'type' => $type,
            'name' => $name,
        ];
        $this->search = '';
        $this->results = [];
        $this->dispatch($this->eventName, $this->selectedGuarantor);
    }

    public function clearSelection()
    {
        $this->selectedGuarantor = null;
        $this->dispatch($this->eventName, null);
    }

    public function render()
    {
        return view('livewire.components.guarantor-select');
    }
}
