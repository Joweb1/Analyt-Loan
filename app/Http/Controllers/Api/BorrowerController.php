<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowerResource;
use App\Models\Borrower;
use Illuminate\Http\Request;

class BorrowerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BorrowerResource::collection(Borrower::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:borrowers,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'nin' => 'nullable|string|max:11',
            'bvn' => 'nullable|string|max:11',
        ]);

        $borrower = Borrower::create($validated);

        return new BorrowerResource($borrower);
    }

    /**
     * Display the specified resource.
     */
    public function show(Borrower $borrower)
    {
        return new BorrowerResource($borrower);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Borrower $borrower)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:borrowers,email,'.$borrower->id,
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|nullable|string',
        ]);

        $borrower->update($validated);

        return new BorrowerResource($borrower);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Borrower $borrower)
    {
        $borrower->delete();

        return response()->json(null, 204);
    }
}
