Based on the documents provided, Analyt Loan is a specialized SaaS (Software as a Service) platform designed as a "Self-Driving Operating System" for micro-lenders and cooperative societies. Its primary goal is to replace manual processes—like spreadsheets and physical notebooks—with an automated, intelligent system that manages the entire lending lifecycle.

## 1. The Core Philosophy: "Self-Driving Money"
The concept centers on the idea that the software should do the heavy lifting so the lender doesn't have to. It operates on three main pillars:
- **The Memory:** It perfectly tracks every loan, borrower, and due date.
- **The Nudge:** It automatically contacts borrowers via Email(PHP Mailer) to remind them of payments, reducing the "forgetfulness" that leads to bad debt.
- **The Engine:** A calculation system that automates interest rates (flat or reducing balance), fees, and penalties with zero manual input.

## 2. Problems It Solves
The documentation highlights that small lenders often lose up to 20% of their capital due to poor organization. Analyt Loan addresses:
- **Blind Spots:** Eliminates the need to manually check books to see who owes money.
- **Manual Labor:** Automates "chasing" borrowers, which is usually the most time-consuming part of lending.
- **Risk Management:** Calculates a "Trust Score" for borrowers based on their payment history to help lenders make better decisions.

## 3. Key Features & Tools
| Feature | Function |
|---|---|
| Automated Email | Acts as an automated collections agent, sending reminders and updates. |
| Trust Score | A proprietary metric that identifies high-risk vs. reliable borrowers. |
| Omnibar Search | A "Google-like" search bar to find any borrower or loan instantly. |
| Nightly Cron Jobs | Backend automation that updates loan statuses and triggers penalties overnight. |

## 4. Technical Structure
The system is built for speed and scale using a MYSQL database for data integrity and Material Design (Roboto Flex) for a clean, intuitive user interface. It is structured into five core sections:
- **Dashboard (Home):** For instant business insights and charts.
- **People (CRM):** To manage borrower profiles.
- **Loans (Portfolio):** To track active and historical lending.
- **Tasks (Collections):** A focused view of who needs to be contacted.
- **Settings (Admin):** For system configuration.

## 6. Customer Directory
A new page that provides a comprehensive overview of all customers (borrowers). It features a card-based layout where each card represents a borrower and displays key information such as their name, location, total debt, and repayment score.

### Key Features:
- **Filters:** Allows filtering borrowers by risk level and region.
- **Grid/List View:** Option to switch between a grid view of borrower cards and a list view.
- **Quick Actions:** On hover, each borrower card reveals quick actions to issue a loan, send a message, or view the borrower's profile.
- **Pagination:** The page includes pagination to navigate through the list of borrowers.

## Recent Feature Implementations and Updates

### Vault Page
- **File:** `resources/views/pages/vault.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/vault` (`name('vault')`) added to `routes/web.php`.
- **Responsiveness:** Improved mobile responsiveness by reducing side padding to `p-0` on sections, and wrapping the main table with `overflow-x-auto`.
- **UI:** "Add Collateral" button moved to the top of the page.

### Customer Registration Page
- **File:** `resources/views/pages/customer-registration.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/customer/create` (`name('customer.create')`) added to `routes/web.php`.
- **Navigation:** "Add Customer" link removed from main navigation. Existing "Add New Customer/Borrower" button on `resources/views/pages/customer.blade.php` modified to navigate to this page.

### Loan Application Page
- **File:** `resources/views/pages/loan-application.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/loan/create` (`name('loan.create')`) added to `routes/web.php`.
- **Navigation:** "New Loan" button on `resources/views/pages/loan.blade.php` modified to navigate to this page.

### Add Collateral Form Page
- **File:** `resources/views/pages/add-collateral.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/collateral/create` (`name('collateral.create')`) added to `routes/web.php`.
- **Navigation:** "Add Collateral" button on `resources/views/pages/vault.blade.php` modified to navigate to this page.

### Collections Page
- **File:** `resources/views/pages/collections.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/collections` (`name('collections')`) added to `routes/web.php`.
- **Navigation:** "Collections" link added to main navigation in `app.blade.php` with `trending_up` icon.
- **Responsiveness:** Improved mobile responsiveness by reducing side padding to `p-0` on the main content div.
- **UI:** "Collection Health (Weekly Progress)" chart section removed.

### Settings Page
- **File:** `resources/views/pages/settings.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/settings` (`name('settings')`) added to `routes/web.php`.
- **Navigation:** Existing "Settings" link in main navigation (`app.blade.php`) modified to point to this page.

### Team Members Page
- **File:** `resources/views/pages/team-members.blade.php` created.
- **Layout:** Integrated into `app.blade.php` layout.
- **Route:** New route `/settings/team-members` (`name('settings.team-members')`) added to `routes/web.php`.
- **Navigation:** "Team Members" link in `resources/views/pages/settings.blade.php` sub-navigation modified to point to this page.
- **Responsiveness:** Improved mobile responsiveness by reducing side padding to `p-0` on the main content div.
- **UI:** "Invite Team Member Modal" component (`resources/views/components/invite-member-modal.blade.php`) created and integrated into `team-members.blade.php` with Alpine.js for show/hide functionality. The modal's close and cancel buttons were updated to control its visibility.
