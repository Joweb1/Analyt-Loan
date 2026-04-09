<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Loan
 *
 * @property string $repayment_status
 * @property \Illuminate\Support\Carbon|null $disbursed_at
 * @property \Illuminate\Support\Carbon|null $due_at
 */
class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loan_number' => $this->loan_number,
            'amount' => (float) $this->amount->getMajorAmount(),
            'interest_rate' => (float) $this->interest_rate,
            'status' => $this->status,
            'repayment_status' => $this->repayment_status,
            'borrower' => new BorrowerResource($this->whenLoaded('borrower')),
            'disbursed_at' => $this->disbursed_at,
            'due_at' => $this->due_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
