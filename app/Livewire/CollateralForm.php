<?php

namespace App\Livewire;

use App\Models\Collateral;
use App\Models\Loan;
use App\Traits\HandlesStorageDisk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CollateralForm extends Component
{
    use HandlesStorageDisk, WithFileUploads;

    public $loan_id;

    public $collateral_id;

    public $name;

    public $type = 'Vehicle';

    public $condition = 'Good';

    public $value;

    public $description;

    public $registered_date;

    public $status = 'in_vault';

    public $image;

    public $current_image;

    public $documents = [];

    // Search & Selection State
    public $searchQuery = '';

    public $searchedLoans = [];

    public $isBranchAsset = false;

    public $selectedLoan = null;

    public function mount()
    {
        $this->loan_id = request()->query('loan_id');
        $this->registered_date = now()->format('Y-m-d');

        if ($this->loan_id) {
            $this->selectLoan($this->loan_id);
        }
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchedLoans = [];

            return;
        }

        $orgId = Auth::user()->organization_id;
        $term = '%'.strtolower(trim($this->searchQuery)).'%';

        $this->searchedLoans = Loan::with('borrower.user')
            ->where('organization_id', $orgId)
            ->where(function ($q) use ($term) {
                $q->where('loan_number', 'like', $term)
                    ->orWhereHas('borrower.user', function ($uq) use ($term) {
                        $uq->whereRaw('LOWER(name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                            ->orWhere('phone', 'like', $term);
                    })
                    ->orWhereHas('borrower', function ($bq) use ($term) {
                        $bq->where('phone', 'like', $term)
                            ->orWhere('bvn', 'like', $term)
                            ->orWhere('national_identity_number', 'like', $term)
                            ->orWhere('custom_id', 'like', $term);
                    });
            })
            ->take(10)
            ->get();
    }

    public function selectLoan($id)
    {
        $this->loan_id = $id;
        $this->selectedLoan = Loan::where('organization_id', Auth::user()->organization_id)
            ->with('borrower.user', 'collateral')
            ->find($id);
        $this->isBranchAsset = false;

        if ($this->selectedLoan && $this->selectedLoan->collateral) {
            $this->collateral_id = $this->selectedLoan->collateral->id;
            $this->fillCollateralData($this->selectedLoan->collateral);
        }
    }

    public function selectBranch()
    {
        $this->loan_id = null;
        $this->selectedLoan = null;
        $this->isBranchAsset = true;
        $this->reset(['name', 'type', 'value', 'condition', 'description', 'status', 'image', 'current_image', 'collateral_id']);
    }

    public function resetSelection()
    {
        $this->loan_id = null;
        $this->selectedLoan = null;
        $this->isBranchAsset = false;
        $this->searchQuery = '';
        $this->searchedLoans = [];
        $this->reset(['name', 'type', 'value', 'condition', 'description', 'status', 'image', 'current_image', 'collateral_id']);
    }

    public function fillCollateralData(Collateral $collateral)
    {
        $this->name = $collateral->name;
        $this->type = $collateral->type;
        $this->condition = $collateral->condition;
        $this->value = $collateral->value->getMajorAmount();
        $this->description = $collateral->description;
        $this->registered_date = $collateral->registered_date ? $collateral->registered_date->format('Y-m-d') : null;
        $this->status = $collateral->status;
        $this->current_image = $collateral->image_url;
        // Documents handling would be complex with livewire file uploads, keeping simple for now
    }

    public function save()
    {
        $this->validate([
            'loan_id' => $this->isBranchAsset ? 'nullable' : 'required|exists:loans,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'value' => 'required|numeric|min:0',
            'condition' => 'required|string',
            'status' => 'required|in:in_vault,returned',
            'image' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ]);

        $data = [
            'organization_id' => Auth::user()->organization_id,
            'loan_id' => $this->loan_id,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'condition' => $this->condition,
            'description' => $this->description,
            'registered_date' => $this->registered_date,
            'status' => $this->status,
        ];

        if ($this->image) {
            $filename = Str::random(40).'.'.$this->image->getClientOriginalExtension();
            $path = 'collaterals/'.$filename;
            $stream = fopen($this->image->getRealPath(), 'r');
            $disk = $this->getStorageDisk();
            Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['image_path'] = $path;
        }

        if ($this->collateral_id) {
            $collateral = Collateral::find($this->collateral_id);
            $collateral->update($data);
            $message = 'Collateral updated successfully.';
        } else {
            Collateral::create($data);
            $message = 'Collateral added successfully.';
        }

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => $message]);

        if ($this->loan_id) {
            return redirect()->route('loan.show', $this->loan_id);
        }

        return redirect()->route('vault');
    }

    public function render()
    {
        return view('livewire.collateral-form')->layout('layouts.app', ['title' => 'Add Collateral']);
    }
}
