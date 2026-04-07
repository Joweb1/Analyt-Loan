<?php

namespace App\Http\Controllers\Api;

use App\Actions\Loans\SynchronizeLoanState;
use App\DTOs\LoanApplicationDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LoanResource::collection(Loan::with('borrower')->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_id' => 'required|uuid|exists:borrowers,id',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'loan_product' => 'required|string',
            'interest_type' => 'required|string',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|string',
            'repayment_cycle' => 'required|string',
            'num_repayments' => 'required|integer|min:1',
        ]);

        $dto = LoanApplicationDTO::fromArray($validated);
        $loan = Loan::create($dto->toArray() + [
            'status' => 'pending',
            'loan_number' => 'LN-'.strtoupper(\Illuminate\Support\Str::random(8)),
        ]);

        return new LoanResource($loan);
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        return new LoanResource($loan->load('borrower'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|string|in:pending,approved,active,repaid,defaulted',
        ]);

        $loan->update($validated);

        if (isset($validated['status']) && $validated['status'] === 'active') {
            app(SynchronizeLoanState::class)->execute($loan);
        }

        return new LoanResource($loan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();

        return response()->json(null, 204);
    }
}
