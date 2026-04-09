<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Borrower
 *
 * @property string $name
 * @property string $email
 */
class BorrowerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'kyc_status' => $this->kyc_status,
            'trust_score' => $this->trust_score,
            'active_loans_count' => $this->loans()->where('status', 'active')->count(),
            'total_debt' => (float) $this->loans()->where('status', 'active')->sum('amount') / 100,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
