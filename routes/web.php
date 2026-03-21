<?php

use App\Livewire\ActionCenter;
use App\Livewire\BorrowerList;
use App\Livewire\OrgRegistrationForm;
use App\Livewire\Settings\FormBuilder;
use App\Livewire\Settings\GeneralSettings;
use App\Livewire\Settings\RolesManagement;
use App\Livewire\Settings\TeamManagement;
use App\Livewire\UserProfile;
use Illuminate\Support\Facades\Route;

// Secure Cron & Queue Triggers (for Shared Hosting)
Route::get('/cron/schedule', [\App\Http\Controllers\CronController::class, 'runSchedule']);
Route::get('/cron/queue', [\App\Http\Controllers\CronController::class, 'runQueue']);

// General Application Routes
Route::view('/', 'pages.welcome');
Route::get('/register-org', OrgRegistrationForm::class)->name('register.org');

Route::middleware(['auth', 'update_last_seen'])->group(function () {
    Route::get('/borrowers', BorrowerList::class)->name('borrowers.index');
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

        return (new \App\Livewire\AdminDashboard)();
    })->middleware('permission:view_dashboard')->name('dashboard');

    // Push Subscription Route
    Route::post('/push-subscription', function (\Illuminate\Http\Request $request) {
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
        Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
        Route::get('/admin/organizations', \App\Livewire\Admin\Organizations::class)->name('admin.organizations');
        Route::get('/admin/distribution', \App\Livewire\Admin\DistributionPanel::class)->name('admin.distribution');
        Route::get('/admin/reports', \App\Livewire\Admin\Reports::class)->name('admin.reports');
        Route::get('/admin/settings', \App\Livewire\Admin\Settings::class)->name('admin.settings');
    });

    Route::middleware(['role:Admin,Loan Analyst,Vault Manager,Credit Analyst,Collection Specialist,Collection Officer'])->group(function () {
        Route::get('status-board', \App\Livewire\StatusBoard::class)->middleware('permission:manage_loans')->name('status-board');
        Route::get('loan', \App\Livewire\LoanDashboard::class)->middleware('permission:manage_loans')->name('loan');
        Route::get('loan/pending', \App\Livewire\PendingLoans::class)->middleware('permission:approve_loans')->name('loans.pending');
        Route::view('loan/create', 'pages.loan-application')->middleware('permission:manage_loans')->name('loan.create');
        Route::get('loan/{loan}/edit', function (\App\Models\Loan $loan) {
            return view('pages.loan-application', ['loan' => $loan]);
        })->middleware('permission:manage_loans')->name('loan.edit');
        Route::get('loan/{loan}', \App\Livewire\LoanDetails::class)->middleware('permission:manage_loans|access_minimal_staff_routes')->name('loan.show');
        Route::get('loan/{loan}/print', \App\Livewire\LoanPrint::class)->middleware('permission:manage_loans')->name('loan.print');
        Route::get('loan/{loan}/repayments/print', \App\Livewire\RepaymentPrint::class)->middleware('permission:manage_loans')->name('repayments.print');
        Route::get('loan/{loan}/schedule/print', \App\Livewire\SchedulePrint::class)->middleware('permission:manage_loans')->name('schedule.print');
        Route::get('borrower/{borrower}/loans', \App\Livewire\UserLoans::class)->middleware('permission:manage_loans|access_minimal_staff_routes')->name('borrower.loans');
        Route::get('borrower/{borrower}/profile', \App\Livewire\BorrowerProfile::class)->middleware('permission:manage_borrowers')->name('borrower.profile');
        Route::get('savings/{borrower}', \App\Livewire\SavingsDetails::class)->middleware('permission:manage_borrowers|access_minimal_staff_routes')->name('savings.show');
        Route::get('savings/{borrower}/print', \App\Livewire\Borrower\SavingsStatementPrint::class)->middleware('permission:export_and_print')->name('savings.print');
        Route::get('actions', ActionCenter::class)->middleware('permission:view_dashboard')->name('actions');
        Route::get('reports', \App\Livewire\Reports::class)->middleware('permission:view_reports')->name('reports');
        Route::get('reports/print/{type}', \App\Livewire\GeneralReportPrint::class)->middleware('permission:export_and_print')->name('report.print');
        Route::get('collections', \App\Livewire\Collections::class)->middleware('permission:manage_collections')->name('collections');
        Route::get('collection-entry', \App\Livewire\CollectionEntry::class)->middleware('permission:enter_collections')->name('collection.entry');
        Route::get('savings-entry', \App\Livewire\SavingsEntry::class)->middleware('permission:enter_savings')->name('savings.entry');
        Route::get('kyc-approval', \App\Livewire\Admin\KycApproval::class)->middleware('permission:approve_kyc')->name('kyc.approval');
        Route::get('loan-approval', \App\Livewire\Admin\LoanApproval::class)->middleware('permission:approve_loans')->name('loan.approval');
        Route::get('repayments', \App\Livewire\RepaymentRecords::class)->middleware('permission:manage_collections')->name('repayments.records');
        Route::get('verifications', \App\Livewire\Admin\PaymentVerifications::class)->middleware('permission:manage_collections')->name('admin.verifications');
        Route::get('notifications', \App\Livewire\Notifications::class)->name('notifications');
        Route::get('settings', GeneralSettings::class)->middleware('permission:manage_settings')->name('settings');
        Route::get('settings/security', \App\Livewire\Settings\SecuritySettings::class)->name('settings.security');
        Route::get('settings/notifications', \App\Livewire\Settings\NotificationSettings::class)->middleware('permission:manage_settings')->name('settings.notifications');
        Route::get('settings/form-builder', FormBuilder::class)->middleware('permission:manage_settings')->name('settings.form-builder');
        Route::get('settings/guarantor-form', \App\Livewire\Settings\GuarantorFormBuilder::class)->middleware('permission:manage_settings')->name('settings.guarantor-form');
        Route::get('settings/roles', RolesManagement::class)->middleware('permission:manage_settings')->name('settings.roles');
        Route::get('settings/team-members', TeamManagement::class)->middleware('permission:manage_settings')->name('settings.team-members');
        Route::get('settings/loan-products', \App\Livewire\Settings\LoanProducts::class)->middleware('permission:manage_settings')->name('settings.loan-products');
        Route::get('settings/portfolios', \App\Livewire\Settings\Portfolios::class)->middleware('permission:manage_settings')->name('settings.portfolios');
        Route::view('customer', 'pages.customer')->middleware('permission:manage_borrowers')->name('customer');
        Route::view('customer/create', 'pages.customer-registration')->middleware('permission:manage_borrowers')->name('customer.create');
        Route::get('customer/guarantor/create', \App\Livewire\Borrower\GuarantorRegistration::class)->middleware('permission:manage_guarantors')->name('guarantor.create');
        Route::get('vault', \App\Livewire\Vault::class)->middleware('permission:manage_vault')->name('vault');
        Route::view('collateral/create', 'pages.add-collateral')->middleware('permission:manage_vault')->name('collateral.create');
    });

    Route::middleware(['role:Borrower'])->prefix('borrower')->name('borrower.')->group(function () {
        Route::get('/home', \App\Livewire\Borrower\Home::class)->name('home');
        Route::get('/alerts', \App\Livewire\Borrower\Alerts::class)->name('alerts');
        Route::get('/borrow', \App\Livewire\Borrower\Borrow::class)->name('borrow');
        Route::get('/repayment', \App\Livewire\Borrower\Repayment::class)->name('repayment');
        Route::get('/activity', \App\Livewire\Borrower\Activity::class)->name('activity');
        Route::get('/account', \App\Livewire\Borrower\Account::class)->name('account');
        Route::get('/account/personal-details', \App\Livewire\Borrower\Account\PersonalDetails::class)->name('account.personal-details');
        Route::get('/account/bank-details', \App\Livewire\Borrower\Account\BankDetails::class)->name('account.bank-details');
        Route::get('/account/loan-agreements', \App\Livewire\Borrower\Account\LoanAgreements::class)->name('account.loan-agreements');
        Route::get('/account/support', \App\Livewire\Borrower\Account\Support::class)->name('account.support');

        // Onboarding
        Route::get('/onboarding/identity', \App\Livewire\Borrower\Onboarding\Identity::class)->name('onboarding.identity');
        Route::get('/onboarding/bank', \App\Livewire\Borrower\Onboarding\Bank::class)->name('onboarding.bank');
        Route::get('/onboarding/employment', \App\Livewire\Borrower\Onboarding\Employment::class)->name('onboarding.employment');
    });
});

require __DIR__.'/auth.php';
