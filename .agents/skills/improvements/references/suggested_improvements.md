# Suggested Improvements Catalog: Analyt Loan

This document catalogs the suggested architectural, performance, and bookkeeping improvements proposed during the codebase investigation.

---

## 1. Asynchronous Background Observers (Queued Listeners)

*   **Problem:** Event listeners like `RecalculateTrustScore`, `SyncLoanSchedule`, and `UpdateBorrowerReadModel` in [AppServiceProvider.php](file:///root/Analyt-Loan/app/Providers/AppServiceProvider.php) execute synchronously during the database transaction of saving a repayment. For high-volume transaction contexts, this degrades api response time and risks UI locks.
*   **Solution:** Convert listeners into queued listeners using Laravel's queue worker system:
    1. Update the listener classes (e.g. `App\Listeners\RecalculateTrustScore`) to implement the `Illuminate\Contracts\Queue\ShouldQueue` interface.
    2. Add standard resilience variables (`$tries`, `$backoff`) to allow safe retries in case of transient database contention.
*   **Target Files:**
    *   [AppServiceProvider.php](file:///root/Analyt-Loan/app/Providers/AppServiceProvider.php)
    *   `app/Listeners/RecalculateTrustScore.php`
    *   `app/Listeners/SyncLoanSchedule.php`
    *   `app/Listeners/UpdateBorrowerReadModel.php`

---

## 2. Row-Level Database Locking (`lockForUpdate`)

*   **Problem:** High-frequency, concurrent requests (e.g., automated debit sweeps, quick successions of repayments) can trigger race conditions. If two processes update the same saver balance or cashbook record at the same time, one balance change might overwrite the other.
*   **Solution:** Implement database row locking in critical read-write paths:
    *   Use database transactions (`DB::transaction`).
    *   Apply `->lockForUpdate()` on selection queries when fetching accounts or cashbook records that will be immediately updated.
*   **Target Files:**
    *   [CashbookService.php](file:///root/Analyt-Loan/app/Services/CashbookService.php) (specifically in `fetchSystemData` and balance computations)
    *   [SynchronizeLoanState.php](file:///root/Analyt-Loan/app/Actions/Loans/SynchronizeLoanState.php) (locking loan and savings records during state updates)

---

## 3. Strict Double-Entry Bookkeeping Ledger

*   **Problem:** The current ledger and cashbook model records system transactions in a single-row "cashbook entry" style. While functional, it is not compliant with standard banking compliance protocols (like GAAP or IFRS).
*   **Solution:** Design a true general ledger structure:
    *   Establish a `Chart of Accounts` (Asset, Liability, Equity, Revenue, Expense accounts).
    *   Re-engineer the transactions system so every event records at least two entries: a **Debit** to one account and a **Credit** to another, ensuring the sum of all debits equals the sum of all credits.
*   **Target Files:**
    *   `app/Models/Transaction.php`
    *   `app/Services/TransactionService.php`
    *   [CashbookService.php](file:///root/Analyt-Loan/app/Services/CashbookService.php)

---

## 4. Multi-Currency Exchange Registry

*   **Problem:** While the [Money](file:///root/Analyt-Loan/app/ValueObjects/Money.php) value object accommodates a currency attribute, the platform lacks a mechanism for converting amounts between different currencies (e.g., converting a loan balance from GHS to NGN for consolidation reports).
*   **Solution:** Introduce an exchange rate service:
    *   Create a `CurrencyExchangeRate` model and repository to pull/store currency conversion rates.
    *   Add a `convertTo` method inside [Money.php](file:///root/Analyt-Loan/app/ValueObjects/Money.php) that checks current rates and converts minor units precisely.
*   **Target Files:**
    *   [Money.php](file:///root/Analyt-Loan/app/ValueObjects/Money.php)
    *   New service `App\Services\CurrencyExchangeService.php`
