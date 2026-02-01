<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FiftyPercentRule implements Rule
{
    /**
     * @var float
     */
    private $loanAmount;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(float $loanAmount)
    {
        $this->loanAmount = $loanAmount;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $collateral = \App\Models\Collateral::find($value);
        
        if (!$collateral) {
            return false;
        }

        return $collateral->value >= ($this->loanAmount / 2);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The collateral value must be at least 50% of the loan amount.';
    }
}
