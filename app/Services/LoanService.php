<?php

namespace App\Services;

use App\Exceptions\CollateralInsufficientException;
use App\Models\Collateral;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Create a new loan.
     */
    public function createLoan(array $data, $attachment = null, $collateralId = null): Loan
    {
        return DB::transaction(function () use ($data, $attachment, $collateralId) {
            $data['organization_id'] ??= Auth::user()->organization_id;

            if ($attachment) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$attachment->getClientOriginalExtension();
                $attachmentPath = 'loan-attachments/'.$filename;
                $stream = fopen($attachment->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($attachmentPath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $data['attachments'] = [$attachmentPath];
            }

            $loan = Loan::create($data);

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }

            return $loan;
        });
    }

    /**
     * Update an existing loan.
     */
    public function updateLoan(Loan $loan, array $data, $attachment = null, $collateralId = null): Loan
    {
        return DB::transaction(function () use ($loan, $data, $attachment, $collateralId) {
            if ($attachment) {
                $filename = \Illuminate\Support\Str::random(40).'.'.$attachment->getClientOriginalExtension();
                $attachmentPath = 'loan-attachments/'.$filename;
                $stream = fopen($attachment->getRealPath(), 'r');
                \Illuminate\Support\Facades\Storage::disk('supabase')->put($attachmentPath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $data['attachments'] = array_merge($loan->attachments ?? [], [$attachmentPath]);
            }

            $loan->update($data);

            if ($collateralId) {
                $this->linkCollateral($loan, $collateralId);
            }

            return $loan;
        });
    }

    /**
     * Activate a loan if collateral is sufficient.
     */
    public function activateLoan(Loan $loan): Loan
    {
        $totalCollateralValue = Collateral::where('loan_id', $loan->id)
            ->where('status', 'in_vault')
            ->sum('value');

        if ($loan->amount > 0 && ($totalCollateralValue / $loan->amount) < 0.5) {
            throw new CollateralInsufficientException('Insufficient collateral to activate the loan.');
        }

        $loan->status = 'active';
        $loan->save();

        return $loan;
    }

    /**
     * Link collateral to a loan.
     */
    protected function linkCollateral(Loan $loan, $collateralId): void
    {
        // Detach previous collateral if any
        Collateral::where('loan_id', $loan->id)->update(['loan_id' => null, 'status' => 'deposited']);

        $collateral = Collateral::find($collateralId);
        if ($collateral) {
            $collateral->update([
                'loan_id' => $loan->id,
                'status' => 'in_vault',
            ]);
        }
    }
}
