<?php

namespace App\Livewire\Components;

use App\Models\Borrower;
use App\Models\Guarantor;
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
        $term = '%'.$this->search.'%';

        // Search Borrowers (Customers)
        $borrowers = Borrower::with('user')
            ->where('organization_id', $orgId)
            ->when($this->excludeId, function ($q) {
                $q->where('user_id', '!=', $this->excludeId);
            })
            ->where(function ($q) use ($term) {
                $q->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                })
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('custom_id', 'like', $term);
            })
            ->take(5)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->user_id, // We link to user_id for internal guarantors
                    'type' => 'internal',
                    'name' => $b->user->name,
                    'subtitle' => 'Customer | '.$b->phone,
                    'custom_id' => $b->custom_id,
                ];
            });

        // Search External Guarantors
        $guarantors = Guarantor::where('organization_id', $orgId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('custom_id', 'like', $term);
            })
            ->take(5)
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'type' => 'external',
                    'name' => $g->name,
                    'subtitle' => 'External Guarantor | '.$g->phone,
                    'custom_id' => $g->custom_id,
                ];
            });

        $this->results = $borrowers->concat($guarantors)->toArray();
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
