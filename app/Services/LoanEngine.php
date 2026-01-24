<?php

namespace App\Services;

use App\Exceptions\CollateralInsufficientException;
use App\Models\Collateral;
use App\Models\Loan;

class LoanEngine
{
    public function activateLoan(Loan $loan)
    {
        $totalCollateralValue = Collateral::where('borrower_id', $loan->borrower_id)
            ->where('status', 'deposited')
            ->sum('market_value');

        if (($totalCollateralValue / $loan->amount_principal) < 0.5) {
            throw new CollateralInsufficientException('Insufficient collateral to activate the loan.');
        }

        $loan->status = 'active';
        $loan->save();

        return $loan;
    }
}
