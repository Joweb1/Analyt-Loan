<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collateral;
use Illuminate\Http\Request;

class CollateralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Collateral::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric',
            'loan_id' => 'required|exists:loans,id',
            'status' => 'required|in:in_vault,returned',
        ]);

        $collateral = Collateral::create($validatedData);

        return response()->json($collateral, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Collateral $collateral)
    {
        return $collateral;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Collateral $collateral)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'value' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:in_vault,returned',
        ]);

        $collateral->update($validatedData);

        return response()->json($collateral, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Collateral $collateral)
    {
        $collateral->delete();

        return response()->json(null, 204);
    }
}
