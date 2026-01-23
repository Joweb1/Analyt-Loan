<?php

namespace App\Livewire;

use App\Models\Collateral;
use Livewire\Component;

class CollateralDetailDrawer extends Component
{
    public $isOpen = false;
    public $collateralId;
    public $collateral;

    public function openDrawer($collateralId)
    {
        $this->collateralId = $collateralId;
        $this->collateral = Collateral::find($collateralId);
        $this->isOpen = true;
    }

    public function closeDrawer()
    {
        $this->isOpen = false;
        $this->collateralId = null;
        $this->collateral = null;
    }

    public function render()
    {
        return view('livewire.components.collateral-detail-drawer');
    }
}
