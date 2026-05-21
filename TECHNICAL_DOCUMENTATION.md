# Technical Documentation & System Architecture

This document provides an in-depth technical overview of the Analyt Loan platform, covering its architecture, core subsystems, configuration, and deployment intricacies. It is intended for developers, system administrators, and technical auditors.

---

## 📖 Navigation
[🏠 Home (README)](README.md) | [⚙️ Technical Docs](TECHNICAL_DOCUMENTATION.md) | [🤝 Contributing](CONTRIBUTING.md)

---

## 1. System Architecture

### 1.1 Technology Stack
*   **Framework:** Laravel 12.x (PHP 8.4)
*   **Frontend:** Livewire 3 (Full-stack reactivity), Volt (Functional API components), Alpine.js, Tailwind CSS 4.
*   **Database:** MySQL 8.0+ (Production) / SQLite (Development).
*   **Queue System:** Database-driven queues for background processing.
*   **Static Analysis:** PHPStan (Level 5).

### 1.2 Multi-Tenancy Design
The application uses a **Shared Database, Shared Schema** multi-tenancy model.
*   **Entity Isolation:** Data isolation is enforced via the `App\Traits\BelongsToOrganization` trait using **Global Scopes**.
*   **Failsafe:** A hard enforcement `where 1=0` is applied if a tenant context is missing for non-system users.
*   **App Owner Exception:** The "App Owner" (Super Admin) bypasses these scopes for platform-wide visibility.

### 1.3 Advanced Architectural Pillars
*   **Financial Precision:** Uses a `Money` Value Object. All internal math is performed on **integers (minor units)** using **BCMath** to prevent floating-point errors.
*   **Financial Idempotency:** Implements an `X-Idempotency-Key` middleware. Prevents duplicate state-changing operations (like disbursements or repayments) within a 24-hour window per user.
*   **Resilience Layer:** Integrated `CircuitBreaker` and `ResilienceService` with **Exponential Backoff** for external API integrations.
*   **Service Layer:** Business logic is decoupled into dedicated services (`LoanService`, `CashbookService`, `TrustScoringService`, etc.).

---

## 2. User & Access Management

### 2.1 User Types (Hardcoded Identity)
The `users.type` column defines the primary identity level and determines which functional roles a user can hold:
*   **`owner`**: The platform creator (App Owner). Can manage organizations and bypass multi-tenancy.
*   **`admin`**: The organization owner/administrator. Full control over their organization's data.
*   **`staff`**: Employees of an organization. Permissions are further refined by Staff-specific Roles.
*   **`customer`**: Borrowers, Savers, or Guarantors. **This is the only type authorized to hold customer-facing functional roles.**

### 2.2 Role-Based Access Control (RBAC)
Implemented via `spatie/laravel-permission`. The system enforces a strict separation between Staff roles and Customer roles:

#### Staff & Admin Roles
*   **Admin**: Inherits all organization permissions (KYC approval, settings, vault).
*   **Loan Analyst / Vault Manager / Credit Analyst**: Specialized roles for back-office operations.
*   **Collection Specialist / Officer**: Roles focused on debt recovery and ledger management.

#### Customer Roles (Restricted to `customer` type)
*   **Borrower**: Authorized to apply for loans and access the borrower self-service portal.
*   **Saver**: Authorized to manage regular savings and high-frequency thrift accounts.
*   **Guarantor**: Limited access for verifying and backing loan applications.

*Note: The system logic prevents assigning Customer roles to Staff/Admin types to maintain strict separation of concerns and prevent unauthorized internal access to customer-specific features.*

### 2.3 Portfolio Scoping
Staff can be assigned to specific **Portfolios**. If assigned, the `BelongsToOrganization` trait further restricts their visibility to only the Borrowers and Loans within their assigned portfolios.

---

## 3. Core Subsystems & Records Hub

### 3.1 The Lending Engine
*   **Lifecycle:** `Application -> KYC Approval -> Loan Approval -> Activation (Disbursement)`.
*   **Reconciliation:** The `SynchronizeLoanState` action matches payments against schedules (Principal -> Interest -> Penalties).
*   **Savings Integration:** Over-payments are automatically routed to a linked `SavingsAccount`.

### 3.2 Records Hub (Digital Registers)
The platform maintains several real-time digital record books:
*   **Loan Disbursement Register:** Monthly grouping of all issued loans with notes and installment tracking.
*   **Daily Savings (Thrift) Record:** A weekly grid for high-frequency collections. Only the "System Today" is editable by staff; admins can unlock past dates for corrections.
*   **Cashbook Dashboard:** Daily reconciliation of **System Inflows** (automatic) vs **Physical Cash at Hand** (manual). Requires a "Shortfall Report" if physical cash is insufficient.
*   **Collection Ledger:** Groups borrowers by "Collection Days" (e.g., Monday Group) to track weekly performance and unpaid indicators.
*   **Savings Withdrawal Ledger:** Tracks withdrawal requests with an audit trail of status changes (Pending -> Approved -> Verified).

### 3.3 Automation (Cron & Queues)
*   **Midnight Sync:** Runs daily to mark overdue loans, apply penalties, and generate staff tasks.
*   **Health Logs:** `SystemHealthService` monitors database size, failed jobs, and server latency.

---

## 3. Environment Configuration (`.env`)

### Database
| Key | Description | Example (Prod) |
| :--- | :--- | :--- |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database server IP/Host | `sql100.iceiy.com` |
| `DB_PORT` | Port number | `3306` |
| `DB_DATABASE` | Database name | `icei_41195783_analytloan` |
| `DB_USERNAME` | Database user | `icei_41195783` |
| `DB_PASSWORD` | Database password | *(Secure)* |

### Application & Security
| Key | Description | Required? |
| :--- | :--- | :--- |
| `APP_URL` | Full URL for asset generation | Yes (`https://domain.com`) |
| `APP_OWNER` | Email of the Super Admin | Yes |
| `CRON_TOKEN` | Secure token for external cron jobs | Yes (Random String) |
| `FILESYSTEM_DISK` | Storage driver | `public` (or `s3`) |

### Feature Flags
| Key | Description | Default |
| :--- | :--- | :--- |
| `VITE_APP_NAME` | Frontend App Name | `${APP_NAME}` |
| `QUEUE_CONNECTION`| Job processing driver | `database` |

---

## 4. System URLs & Endpoints

### 4.1 Automation Triggers (Secure)
These URLs must be called by an external cron service (e.g., cron-job.org).

*   **Run Scheduler:**
    *   `GET /cron/schedule?token=YOUR_CRON_TOKEN`
    *   *Effect:* Runs `php artisan schedule:run`
*   **Run Queue:**
    *   `GET /cron/queue?token=YOUR_CRON_TOKEN`
    *   *Effect:* Runs `php artisan queue:work --stop-when-empty`

### 4.2 Deployment Utility (Temporary)
*   **Setup Script:**
    *   `GET /deploy-setup.php?token=YOUR_DB_PASSWORD`
    *   *Effect:* Runs migrations and creates storage symlinks.
    *   *Argument:* Add `&seed=true` to seed default data.

### 4.3 Key Application Routes
*   `/dashboard`: Intelligent redirect based on user role (Admin vs. Borrower).
*   `/loan/create`: Loan application wizard.
*   `/admin/settings`: Organization-level configuration.
*   `/borrower/home`: Borrower self-service portal.

---

## 5. Deployment Guide (cPanel Specific)

### 5.1 Directory Structure
For security, the application code should sit **outside** `public_html`.

```text
/home/username/
├── analyt-loan/         <-- Repository content here
│   ├── app/
│   ├── .env
│   └── ...
└── public_html/         <-- Web Root
    └── (Symlink to /home/username/analyt-loan/public)
```

### 5.2 Storage Linking
Laravel requires a symlink from `public/storage` to `storage/app/public`.
*   **Standard Command:** `php artisan storage:link`
*   **Shared Hosting Workaround:** The `/deploy-setup.php` script handles this automatically if SSH is unavailable.

### 5.3 Post-Deployment Checklist
1.  Update `.env` with production DB credentials and `CRON_TOKEN`.
2.  Visit `/deploy-setup.php?token=...` to migrate database.
3.  Configure external Cron Jobs to hit the `/cron/...` endpoints.
4.  **Delete** `/deploy-setup.php` immediately.
