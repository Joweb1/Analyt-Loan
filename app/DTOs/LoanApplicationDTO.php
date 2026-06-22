<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class LoanApplicationDTO
{
    public function __construct(
        public readonly string $borrower_id,
        public readonly string $loan_product,
        public readonly float $amount,
        public readonly float $interest_rate,
        public readonly string $interest_calculation_type,
        public readonly string $interest_type,
        public readonly string $interest_cycle,
        public readonly int $duration,
        public readonly string $duration_unit,
        public readonly string $repayment_cycle,
        public readonly int $num_repayments,
        public readonly ?string $release_date = null,
        public readonly ?float $processing_fee = 0,
        public readonly ?string $processing_fee_type = 'fixed',
        public readonly ?float $insurance_fee = 0,
        public readonly ?string $insurance_fee_type = 'fixed',
        public readonly ?string $portfolio_id = null,
        public readonly ?string $collection_group = null,
        public readonly ?string $loan_officer_id = null,
        public readonly ?string $guarantor_id = null,
        public readonly ?string $external_guarantor_id = null,
        public readonly ?string $description = null,
        public readonly ?string $loan_number = null,
    ) {}

    /**
     * Create a DTO from a request or array.
     */
    public static function fromArray(array $data): self
    {
        $sanitize = fn ($val) => (is_string($val) && trim($val) === '') ? null : $val;

        return new self(
            borrower_id: $data['borrower_id'],
            loan_product: $data['loan_product'],
            amount: (float) $data['amount'],
            interest_rate: (float) $data['interest_rate'],
            interest_calculation_type: $data['interest_calculation_type'] ?? 'percentage',
            interest_type: $data['interest_type'],
            interest_cycle: $data['interest_cycle'] ?? 'month',
            duration: (int) $data['duration'],
            duration_unit: $data['duration_unit'],
            repayment_cycle: $data['repayment_cycle'],
            num_repayments: (int) $data['num_repayments'],
            release_date: $sanitize($data['release_date'] ?? null),
            processing_fee: (float) ($data['processing_fee'] ?? 0),
            processing_fee_type: $data['processing_fee_type'] ?? 'fixed',
            insurance_fee: (float) ($data['insurance_fee'] ?? 0),
            insurance_fee_type: $data['insurance_fee_type'] ?? 'fixed',
            portfolio_id: $sanitize($data['portfolio_id'] ?? null),
            collection_group: $sanitize($data['collection_group'] ?? null),
            loan_officer_id: $sanitize($data['loan_officer_id'] ?? null),
            guarantor_id: $sanitize($data['guarantor_id'] ?? null),
            external_guarantor_id: $sanitize($data['external_guarantor_id'] ?? null),
            description: $sanitize($data['description'] ?? null),
            loan_number: $sanitize($data['loan_number'] ?? null),
        );
    }

    /**
     * Convert DTO back to array for model creation.
     */
    public function toArray(): array
    {
        $data = [
            'borrower_id' => $this->borrower_id,
            'loan_product' => $this->loan_product,
            'amount' => $this->amount,
            'interest_rate' => $this->interest_rate,
            'interest_calculation_type' => $this->interest_calculation_type,
            'interest_type' => $this->interest_type,
            'interest_cycle' => $this->interest_cycle,
            'duration' => $this->duration,
            'duration_unit' => $this->duration_unit,
            'repayment_cycle' => $this->repayment_cycle,
            'num_repayments' => $this->num_repayments,
            'release_date' => $this->release_date,
            'processing_fee' => $this->processing_fee,
            'processing_fee_type' => $this->processing_fee_type,
            'insurance_fee' => $this->insurance_fee,
            'insurance_fee_type' => $this->insurance_fee_type,
            'portfolio_id' => $this->portfolio_id,
            'collection_group' => $this->collection_group,
            'loan_officer_id' => $this->loan_officer_id,
            'guarantor_id' => $this->guarantor_id,
            'external_guarantor_id' => $this->external_guarantor_id,
            'description' => $this->description,
        ];

        if ($this->loan_number !== null) {
            $data['loan_number'] = $this->loan_number;
        }

        return $data;
    }
}
