<?php

namespace App\Observers;

use App\Models\Collateral;
use App\Helpers\SystemLogger;

class CollateralObserver
{
    /**
     * Handle the Collateral "created" event.
     */
    public function created(Collateral $collateral): void
    {
        $context = $collateral->loan 
            ? "to Loan #{$collateral->loan->loan_number}" 
            : "as Company Asset";

        SystemLogger::success(
            'New Collateral Added',
            "Collateral '{$collateral->name}' (₦" . number_format($collateral->value) . ") has been added {$context}.",
            'collateral',
            $collateral
        );
    }

    /**
     * Handle the Collateral "updated" event.
     */
    public function updated(Collateral $collateral): void
    {
        if ($collateral->isDirty(['name', 'value', 'status', 'condition'])) {
            $context = $collateral->loan 
                ? "on Loan #{$collateral->loan->loan_number}" 
                : "Company Asset";

            SystemLogger::log(
                'Collateral Updated',
                "Details for collateral '{$collateral->name}' ({$context}) have been updated.",
                'info',
                'collateral',
                $collateral
            );
        }
    }

    /**
     * Handle the Collateral "deleted" event.
     */
    public function deleted(Collateral $collateral): void
    {
        $context = $collateral->loan 
            ? "from Loan #{$collateral->loan->loan_number}" 
            : "";

        SystemLogger::warning(
            'Collateral Removed',
            "Collateral '{$collateral->name}' has been removed {$context}.",
            'collateral',
            $collateral
        );
    }

    /**
     * Handle the Collateral "restored" event.
     */
    public function restored(Collateral $collateral): void
    {
        //
    }

    /**
     * Handle the Collateral "force deleted" event.
     */
    public function forceDeleted(Collateral $collateral): void
    {
        //
    }
}
