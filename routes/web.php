<?php

use App\Livewire\ActionCenter;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DistributionPanel;
use App\Livewire\Admin\KycApproval;
use App\Livewire\Admin\LoanApproval;
use App\Livewire\Admin\Organizations;
use App\Livewire\Admin\PaymentVerifications;
use App\Livewire\Admin\Settings;
use App\Livewire\AdminDashboard;
use App\Livewire\Borrower\Account;
use App\Livewire\Borrower\Account\BankDetails;
use App\Livewire\Borrower\Account\LoanAgreements;
use App\Livewire\Borrower\Account\PersonalDetails;
use App\Livewire\Borrower\Account\Support;
use App\Livewire\Borrower\Activity;
use App\Livewire\Borrower\Alerts;
use App\Livewire\Borrower\Borrow;
use App\Livewire\Borrower\GuarantorRegistration;
use App\Livewire\Borrower\Home;
use App\Livewire\Borrower\Onboarding\Bank;
use App\Livewire\Borrower\Onboarding\Employment;
use App\Livewire\Borrower\Onboarding\Identity;
use App\Livewire\Borrower\Repayment;
use App\Livewire\Borrower\SavingsStatementPrint;
use App\Livewire\BorrowerProfile;
use App\Livewire\Cashbook\BudgetManager;
use App\Livewire\Cashbook\MonthRecord;
use App\Livewire\CollectionEntry;
use App\Livewire\Collections;
use App\Livewire\CustomerList;
use App\Livewire\DailySavings\Record;
use App\Livewire\DisbursementRegister;
use App\Livewire\GeneralReportPrint;
use App\Livewire\GuarantorProfile;
use App\Livewire\Ledger\GroupLedger;
use App\Livewire\LoanDashboard;
use App\Livewire\LoanDetails;
use App\Livewire\LoanPrint;
use App\Livewire\Notifications;
use App\Livewire\OrgRegistrationForm;
use App\Livewire\PendingLoans;
use App\Livewire\Records;
use App\Livewire\RepaymentPrint;
use App\Livewire\RepaymentRecords;
use App\Livewire\Reports;
use App\Livewire\SaverProfile;
use App\Livewire\SavingsDetails;
use App\Livewire\SavingsEntry;
use App\Livewire\SavingsWithdrawalRegister;
use App\Livewire\SchedulePrint;
use App\Livewire\Settings\FormBuilder;
use App\Livewire\Settings\GeneralSettings;
use App\Livewire\Settings\LoanProducts;
use App\Livewire\Settings\NotificationSettings;
use App\Livewire\Settings\Portfolios;
use App\Livewire\Settings\RolesManagement;
use App\Livewire\Settings\SecuritySettings;
use App\Livewire\Settings\TeamManagement;
use App\Livewire\StatusBoard;
use App\Livewire\TransactionHistory;
use App\Livewire\UserLoans;
use App\Livewire\UserProfile;
use App\Livewire\Vault;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Secure Cron & Queue Triggers (for Shared Hosting)

// General Application Routes
Route::view('/', 'pages.welcome');
Route::get('/register-org', OrgRegistrationForm::class)->name('register.org');
Volt::route('/authlog', 'pages.auth-monitor')->name('auth.monitor');

Route::middleware(['auth', 'update_last_seen'])->group(function () {
    Route::get('profile', UserProfile::class)->name('profile');

    Route::get('dashboard', function () {
        $user = auth()->user();
        if ($user->isAppOwner()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole(['Borrower'])) {
            return redirect()->route('borrower.home');
        }
        if ($user->hasRole(['Collection Officer'])) {
            return redirect()->route('collections');
        }

        return (new AdminDashboard)();
    })->middleware('permission:view_dashboard')->name('dashboard');

    // Push Subscription Route
    Route::post('/push-subscription', function (Request $request) {
        $request->validate([
            'endpoint' => 'required',
            'keys.auth' => 'required',
            'keys.p256dh' => 'required',
        ]);

        $request->user()->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json(['success' => true]);
    });

    // App Owner Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/dashboard', Dashboard::class)->name('admin.dashboard');

        Route::get('/admin/organizations', Organizations::class)->name('admin.organizations');
        Route::get('/admin/distribution', DistributionPanel::class)->name('admin.distribution');
        Route::get('/admin/reports', App\Livewire\Admin\Reports::class)->name('admin.reports');
        Route::get('/admin/settings', Settings::class)->name('admin.settings');
    });

    Route::middleware(['role:Admin,Loan Analyst,Vault Manager,Credit Analyst,Collection Specialist,Collection Officer'])->group(function () {
        Route::get('status-board', StatusBoard::class)->middleware('permission:manage_loans')->name('status-board');
        Route::get('loan', LoanDashboard::class)->middleware('permission:manage_loans')->name('loan');
        Route::get('records', Records::class)->middleware('permission:manage_loans|record_cashbook')->name('records');
        Route::get('loan/disbursement-register', DisbursementRegister::class)->middleware('permission:manage_loans')->name('loan.disbursement-register');
        Route::get('loan/pending', PendingLoans::class)->middleware('permission:approve_loans')->name('loans.pending');
        Route::view('loan/create', 'pages.loan-application')->middleware('permission:manage_loans')->name('loan.create');
        Route::get('loan/{loan}/edit', function (Loan $loan) {
            return view('pages.loan-application', ['loan' => $loan]);
        })->middleware('permission:manage_loans')->name('loan.edit');
        Route::get('loan/{loan}', LoanDetails::class)->middleware('permission:manage_loans|access_minimal_staff_routes')->name('loan.show');
        Route::get('loan/{loan}/print', LoanPrint::class)->middleware('permission:manage_loans')->name('loan.print');
        Route::get('loan/{loan}/repayments/print', RepaymentPrint::class)->middleware('permission:manage_loans')->name('repayments.print');
        Route::get('loan/{loan}/schedule/print', SchedulePrint::class)->middleware('permission:manage_loans')->name('schedule.print');
        Route::get('borrower/{borrower}/loans', UserLoans::class)->middleware('permission:manage_loans|access_minimal_staff_routes')->name('borrower.loans');
        Route::get('borrower/{borrower}/profile', BorrowerProfile::class)->middleware('permission:manage_borrowers')->name('borrower.profile');
        Route::get('saver/{saver}/profile', SaverProfile::class)->middleware('permission:manage_borrowers')->name('saver.profile');
        Route::get('guarantor/{guarantor}/profile', GuarantorProfile::class)->middleware('permission:manage_borrowers')->name('guarantor.profile');
        Route::get('savings/withdrawal-ledger', SavingsWithdrawalRegister::class)->middleware('permission:manage_borrowers')->name('savings.withdrawals');
        Route::get('savings/{user}', SavingsDetails::class)->middleware('permission:manage_borrowers|access_minimal_staff_routes')->name('savings.show');
        Route::get('savings/{user}/print', SavingsStatementPrint::class)->middleware('permission:export_and_print')->name('savings.print');
        Route::get('actions', ActionCenter::class)->middleware('permission:view_dashboard')->name('actions');
        Route::get('reports', Reports::class)->middleware('permission:view_reports')->name('reports');
        Route::get('reports/print/{type}', GeneralReportPrint::class)->middleware('permission:export_and_print')->name('report.print');
        Route::get('collections', Collections::class)->middleware('permission:manage_collections')->name('collections');
        Route::get('collection-entry', CollectionEntry::class)->middleware('permission:enter_collections')->name('collection.entry');
        Route::get('savings-entry', SavingsEntry::class)->middleware('permission:enter_savings')->name('savings.entry');
        Route::get('kyc-approval', KycApproval::class)->middleware('permission:approve_kyc')->name('kyc.approval');
        Route::get('loan-approval', LoanApproval::class)->middleware('permission:approve_loans')->name('loan.approval');
        Route::get('repayments', RepaymentRecords::class)->middleware('permission:manage_collections')->name('repayments.records');
        Route::get('transactions', TransactionHistory::class)->middleware('permission:view_reports')->name('transactions');
        Route::get('ledger', App\Livewire\Ledger\Dashboard::class)->middleware('permission:manage_collections')->name('ledger.dashboard');
        Route::get('ledger/group/{group}', GroupLedger::class)->middleware('permission:manage_collections')->name('ledger.group');
        Route::get('daily-savings', Record::class)->middleware('permission:manage_collections')->name('daily-savings.record');
        Route::get('verifications', PaymentVerifications::class)->middleware('permission:manage_collections')->name('admin.verifications');
        Route::get('notifications', Notifications::class)->name('notifications');
        Route::get('settings', GeneralSettings::class)->middleware('permission:manage_settings')->name('settings');
        Route::get('settings/security', SecuritySettings::class)->name('settings.security');
        Route::get('settings/notifications', NotificationSettings::class)->middleware('permission:manage_settings')->name('settings.notifications');
        Route::get('settings/form-builder', FormBuilder::class)->middleware('permission:manage_settings')->name('settings.form-builder');
        Route::get('settings/roles', RolesManagement::class)->middleware('permission:manage_settings')->name('settings.roles');
        Route::get('settings/team-members', TeamManagement::class)->middleware('permission:manage_settings')->name('settings.team-members');
        Route::get('settings/loan-products', LoanProducts::class)->middleware('permission:manage_settings')->name('settings.loan-products');
        Route::get('settings/portfolios', Portfolios::class)->middleware('permission:manage_settings')->name('settings.portfolios');
        Route::get('customers', CustomerList::class)->middleware('permission:manage_borrowers')->name('customer');
        Route::get('customer/create/{type?}', function ($type = 'borrower') {
            return view('pages.customer-registration', ['type' => $type]);
        })->middleware('permission:manage_borrowers')->name('customer.create');
        Route::get('customer/guarantor/create', GuarantorRegistration::class)->middleware('permission:manage_guarantors')->name('guarantor.create');
        Route::get('vault', Vault::class)->middleware('permission:manage_vault')->name('vault');
        Route::get('cashbook', App\Livewire\Cashbook\Dashboard::class)->middleware('permission:manage_vault|record_cashbook')->name('cashbook');
        Route::get('cashbook/month-record', MonthRecord::class)->middleware('permission:manage_vault|record_cashbook')->name('cashbook.month-record');
        Route::get('cashbook/budget', BudgetManager::class)->middleware('permission:manage_settings')->name('cashbook.budget');
        Route::view('collateral/create', 'pages.add-collateral')->middleware('permission:manage_vault')->name('collateral.create');
    });

    Route::middleware(['role:Borrower'])->prefix('borrower')->name('borrower.')->group(function () {
        Route::get('/home', Home::class)->name('home');
        Route::get('/alerts', Alerts::class)->name('alerts');
        Route::get('/borrow', Borrow::class)->name('borrow');
        Route::get('/repayment', Repayment::class)->name('repayment');
        Route::get('/activity', Activity::class)->name('activity');
        Route::get('/account', Account::class)->name('account');
        Route::get('/account/personal-details', PersonalDetails::class)->name('account.personal-details');
        Route::get('/account/bank-details', BankDetails::class)->name('account.bank-details');
        Route::get('/account/loan-agreements', LoanAgreements::class)->name('account.loan-agreements');
        Route::get('/account/support', Support::class)->name('account.support');

        // Onboarding
        Route::get('/onboarding/identity', Identity::class)->name('onboarding.identity');
        Route::get('/onboarding/bank', Bank::class)->name('onboarding.bank');
        Route::get('/onboarding/employment', Employment::class)->name('onboarding.employment');
    });
});

require __DIR__.'/auth.php';
