# Agent Guidelines & Codebase Standards: Analyt Loan

Welcome! This document defines the engineering standards, architecture constraints, and styling patterns of the **Analyt Loan** platform. All changes must adhere strictly to these rules.

---

## 1. Tenancy & Data Isolation (The "Fortress")

*   **Enforcement:** Tenancy is isolated by the [BelongsToOrganization](file:///root/Analyt-Loan/app/Traits/BelongsToOrganization.php) trait using global scopes. 
*   **Safety Rule:** Never write raw SQL queries that bypass Eloquent models unless you explicitly include `where('organization_id', ...)` constraints.
*   **Failsafe Check:** The global scope enforces `1 = 0` if a tenant context is missing for non-system users. If writing background jobs, make sure to set the organization context using the `TenantSession` singleton or bypass scopes explicitly using `withoutGlobalScopes()` if performing organizational sweeps.
*   **Portfolio Scoping:** For staff-level users, query scopes are automatically restricted to assigned portfolios for the `Borrower` and `Loan` models.

---

## 2. Financial Precision & Calculations

*   **Integer Math (Minor Units):** All database currency fields are stored as `BIGINT` minor units (e.g. cents, kobo).
*   **Arithmetic Value Object:** Never use PHP floats for arithmetic calculations. Use the [Money](file:///root/Analyt-Loan/app/ValueObjects/Money.php) value object.
    *   *Correct:* `$balance = $principal->add($interest);`
    *   *Incorrect:* `$balance = $principal + $interest;`
*   **BCMath Engine:** Under the hood, [Money](file:///root/Analyt-Loan/app/ValueObjects/Money.php) wraps `bcmul` and `bcdiv` to prevent standard binary floating-point rounding errors.
*   **Formatting:** Formatting (e.g., `format()` method) rounds money values to whole numbers for user displays, while computations inside actions retain minor unit precision.

---

## 3. Resilience & External API Integrations

*   **Circuit Breakers:** All outbound integrations (Supabase filesystems, payment gateways, push notification channels) must run through the [CircuitBreaker](file:///root/Analyt-Loan/app/Services/CircuitBreaker.php) service.
*   **Resilience Service:** Use [ResilienceService](file:///root/Analyt-Loan/app/Services/ResilienceService.php) to wrap API calls with exponential backoff retries and Sentry-based transaction tracing:
    ```php
    ResilienceService::execute('storage', 'Fetch Signed URL', function () {
        return app(StorageProvider::class)->url($path);
    }, $fallbackUrl);
    ```

---

## 4. Code Standards & Architecture Patterns

*   **Strict Types:** Always declare `declare(strict_types=1);` at the top of new PHP files.
*   **Pint Linting:** Ensure all edits comply with Pint coding styles (PSR-12 alignment). Run `./vendor/bin/pint` to auto-fix code style issues.
*   **Static Analysis:** Keep the codebase PHPStan Level 5 compliant. Do not ignore type warnings without strong architectural justification.
*   **Logic Isolation (Actions & Services):** 
    *   *Models:* Contain relations, scopes, and basic accessors. Do not place business state-changing logic in models.
    *   *Actions:* Create single-purpose action classes (e.g. [SynchronizeLoanState](file:///root/Analyt-Loan/app/Actions/Loans/SynchronizeLoanState.php)) to execute workflows.
    *   *Services:* Decouple external domain features (reconciliation, scoring, localization) into injectable services.
    *   *Livewire Component Controllers:* Keep them thin. Their job is to manage UI binding state, validate input, and dispatch transactions to actions or services.

---

## 5. Frontend UI/UX Standards

*   **Stack:** Livewire 3 + Alpine.js, Tailwind CSS 4 styling.
*   **Reactivity:** Leverage Livewire's reactive binding and Alpine.js micro-interactions (collapsing drawers, showing modals, on-hover quick actions) to avoid page refreshes.
*   **Mobile Responsiveness:** All layouts must render perfectly on standard mobile viewports. Wrap data lists in overflow scrollable elements (`overflow-x-auto`) and reduce screen padding to `p-0` on smaller screens.
*   **Design Aesthetics:** Use curated premium colors (HSLTailored, sleek dark modes, dynamic gradients) rather than browser defaults or plain red/blue/green.

---

## 6. Pre-Commit Quality Checks

Whenever you modify any codebase file, you MUST verify that:
1.  **Pint** lints files cleanly without syntax or styling errors.
2.  **PHPStan** successfully passes static analysis at Level 5.
3.  **PHPUnit** test suite passes completely.
Use the `quality_check` script skill to automate these steps.
