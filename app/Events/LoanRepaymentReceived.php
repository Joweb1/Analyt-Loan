<?php

namespace App\Events;

use App\Models\Loan;
use App\Models\Repayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanRepaymentReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Loan $loan,
        public ?Repayment $repayment = null
    ) {}
}
