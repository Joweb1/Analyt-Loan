<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organization_id
 * @property \Illuminate\Support\Carbon $entry_date
 * @property string|null $description
 * @property \App\ValueObjects\Money $loan_repayments
 * @property \App\ValueObjects\Money $savings_deposits
 * @property \App\ValueObjects\Money $registration_fees
 * @property \App\ValueObjects\Money $loan_processing_fees
 * @property \App\ValueObjects\Money $insurance_fees
 * @property \App\ValueObjects\Money $bank_withdrawals
 * @property \App\ValueObjects\Money $excess_cash
 * @property \App\ValueObjects\Money $loan_disbursements
 * @property \App\ValueObjects\Money $savings_withdrawals
 * @property \App\ValueObjects\Money $daily_expense_amount
 * @property \App\ValueObjects\Money $opening_cash
 * @property \App\ValueObjects\Money $expected_cash_at_hand
 * @property \App\ValueObjects\Money $actual_cash_at_hand
 * @property \App\ValueObjects\Money $bank_deposit_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property string|null $audit_hash
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\ValueObjects\Money $card_payments
 * @property \App\ValueObjects\Money $default_amount
 * @property \App\ValueObjects\Money $charges
 * @property \App\ValueObjects\Money $bonuses
 * @property string|null $shortfall_report
 * @property \App\ValueObjects\Money $daily_savings
 * @property \App\ValueObjects\Money $loan_interest
 * @property \App\ValueObjects\Money $expected_bank_transfers
 * @property-read mixed $daily_net
 * @property-read mixed $expected_deposit
 * @property-read mixed $total_inflow
 * @property-read mixed $total_outflow
 * @property-read \App\Models\Organization $organization
 *
 * @method static \Database\Factories\CashbookEntryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereActualCashAtHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereAuditHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereBankDepositAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereBankWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereBonuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereCardPayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereDailyExpenseAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereDailySavings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereDefaultAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereEntryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereExcessCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereExpectedBankTransfers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereExpectedCashAtHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereInsuranceFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereLoanDisbursements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereLoanInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereLoanProcessingFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereLoanRepayments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereOpeningCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereRegistrationFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereSavingsDeposits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereSavingsWithdrawals($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereShortfallReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashbookEntry whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class CashbookEntry extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id',
        'entry_date',
        'description',
        'loan_repayments',
        'loan_interest',
        'savings_deposits',
        'daily_savings',
        'registration_fees',
        'loan_processing_fees',
        'insurance_fees',
        'card_payments',
        'excess_cash',
        'default_amount',
        'loan_disbursements',
        'savings_withdrawals',
        'bank_withdrawals',
        'charges',
        'bonuses',
        'daily_expense_amount',
        'opening_cash',
        'expected_cash_at_hand',
        'actual_cash_at_hand',
        'expected_bank_transfers',
        'bank_deposit_amount',
        'status',
        'verified_at',
        'audit_hash',
        'shortfall_report',
    ];

    protected $attributes = [
        'loan_repayments' => 0,
        'loan_interest' => 0,
        'savings_deposits' => 0,
        'daily_savings' => 0,
        'registration_fees' => 0,
        'loan_processing_fees' => 0,
        'insurance_fees' => 0,
        'card_payments' => 0,
        'excess_cash' => 0,
        'default_amount' => 0,
        'loan_disbursements' => 0,
        'savings_withdrawals' => 0,
        'bank_withdrawals' => 0,
        'charges' => 0,
        'bonuses' => 0,
        'daily_expense_amount' => 0,
        'opening_cash' => 0,
        'expected_cash_at_hand' => 0,
        'actual_cash_at_hand' => 0,
        'expected_bank_transfers' => 0,
        'bank_deposit_amount' => 0,
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date:Y-m-d',
            'loan_repayments' => MoneyCast::class,
            'loan_interest' => MoneyCast::class,
            'savings_deposits' => MoneyCast::class,
            'daily_savings' => MoneyCast::class,
            'registration_fees' => MoneyCast::class,
            'loan_processing_fees' => MoneyCast::class,
            'insurance_fees' => MoneyCast::class,
            'card_payments' => MoneyCast::class,
            'excess_cash' => MoneyCast::class,
            'default_amount' => MoneyCast::class,
            'loan_disbursements' => MoneyCast::class,
            'savings_withdrawals' => MoneyCast::class,
            'bank_withdrawals' => MoneyCast::class,
            'charges' => MoneyCast::class,
            'bonuses' => MoneyCast::class,
            'daily_expense_amount' => MoneyCast::class,
            'opening_cash' => MoneyCast::class,
            'expected_cash_at_hand' => MoneyCast::class,
            'actual_cash_at_hand' => MoneyCast::class,
            'expected_bank_transfers' => MoneyCast::class,
            'bank_deposit_amount' => MoneyCast::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Calculate the total inflow for the day.
     */
    public function getTotalInflowAttribute()
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        $zero = new \App\ValueObjects\Money(0, $currency);

        return ($this->loan_repayments ?? $zero)
            ->add($this->loan_interest ?? $zero)
            ->add($this->savings_deposits ?? $zero)
            ->add($this->daily_savings ?? $zero)
            ->add($this->registration_fees ?? $zero)
            ->add($this->loan_processing_fees ?? $zero)
            ->add($this->insurance_fees ?? $zero)
            ->add($this->card_payments ?? $zero)
            ->add($this->default_amount ?? $zero) // Inflow
            ->add($this->excess_cash ?? $zero);
    }

    /**
     * Calculate the total outflow for the day.
     */
    public function getTotalOutflowAttribute()
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        $zero = new \App\ValueObjects\Money(0, $currency);

        return ($this->loan_disbursements ?? $zero)
            ->add($this->savings_withdrawals ?? $zero)
            ->add($this->bank_withdrawals ?? $zero)
            ->add($this->charges ?? $zero)
            ->add($this->bonuses ?? $zero);
    }

    /**
     * The reconciliation target for the bank deposit.
     */
    public function getExpectedDepositAttribute()
    {
        return $this->total_inflow;
    }

    /**
     * Calculate the daily net balance (discrepancy).
     * In the bank-centric model, Bank Deposit should equal Expected Deposit (Total Inflow).
     */
    public function getDailyNetAttribute()
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        $zero = new \App\ValueObjects\Money(0, $currency);

        return ($this->bank_deposit_amount ?? $zero)->subtract($this->expected_deposit ?? $zero);
    }
}
