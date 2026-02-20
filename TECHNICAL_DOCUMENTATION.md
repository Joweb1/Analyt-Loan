# Technical Documentation & System Architecture

This document provides an in-depth technical overview of the Analyt Loan platform, covering its architecture, core subsystems, configuration, and deployment intricacies. It is intended for developers, system administrators, and technical auditors.

---

## 1. System Architecture

### 1.1 Technology Stack
*   **Framework:** Laravel 12.x (PHP 8.4)
*   **Frontend:** Livewire 3 (Full-stack reactivity), Volt (Functional API components), Alpine.js, Tailwind CSS 4.
*   **Database:** MySQL 8.0+ (Production) / SQLite (Development).
*   **Queue System:** Database-driven queues for background processing (Emails, Push Notifications).
*   **Static Analysis:** Larastan (PHPStan Level 5).

### 1.2 Multi-Tenancy Design
The application uses a **Shared Database, Shared Schema** multi-tenancy model.
*   **Entity Isolation:** Data isolation is enforced at the Eloquent model level using the `App\Traits\BelongsToOrganization` trait.
*   **Global Scope:** A global scope automatically applies `where organization_id = ?` to all queries based on the authenticated user's organization.
*   **App Owner Exception:** The "App Owner" (super admin) bypasses these scopes to view platform-wide data.

### 1.3 Service Layer Pattern
Business logic is decoupled from Controllers/Livewire components into dedicated services:
*   **`LoanService`**: Handles loan creation, attachment storage, and collateral linking.
*   **`TrustScoringService`**: (Proprietary) Calculates borrower reliability based on repayment timeliness.
*   **`ActionTaskService`**: Generates daily actionable tasks for staff (e.g., "Call overdue borrower X").
*   **`SystemHealthService`**: Logs critical system events and health metrics.

---

## 2. Core Subsystems

### 2.1 The Lending Engine (`LoanService` & `LoanObserver`)
The lifecycle of a loan is event-driven:
1.  **Application:** Borrower applies -> `LoanObserver::created` triggers alerts.
2.  **Approval:** Staff approves -> Status changes to `approved`.
3.  **Activation:**
    *   Logic: Checks if Collateral Value / Loan Amount >= 50% (via `FiftyPercentRule`).
    *   Action: Status `active`, funds "disbursed" (logical).
4.  **Repayment:**
    *   `refreshRepaymentStatus()`: A crucial method in the `Loan` model. It acts as a reconciliation engine, matching total paid amounts against the schedule (Principal -> Interest -> Penalties).
    *   Automatic Closure: If `total_paid >= total_due`, status becomes `repaid`.

### 2.2 Automation (Cron & Queues)
Shared hosting environments often lack SSH access, so automation is triggered via secure HTTP endpoints.

*   **Scheduler (`app:midnight-sync`):**
    *   Runs daily at midnight.
    *   Marks loans as `overdue`.
    *   Applies recurring penalties.
    *   Recalculates Trust Scores.
    *   Generates daily staff tasks.
*   **Queue Worker:**
    *   Processes `PushSystemNotification` and email jobs.
    *   Should be triggered every minute to ensure near-real-time alerts.

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
