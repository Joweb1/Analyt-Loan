<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BorrowerList;
use App\Livewire\Admin\DistributionPanel;
use App\Livewire\Settings\FormBuilder;
use App\Livewire\Settings\GeneralSettings;
use App\Livewire\Settings\RolesManagement;
use App\Livewire\Settings\TeamManagement;
use App\Livewire\UserProfile;
use App\Livewire\ActionCenter;
use App\Livewire\OrgRegistrationForm;
use App\Livewire\Borrower\Dashboard as BorrowerDashboard;

Route::get('/borrowers', BorrowerList::class)->name('borrowers.index');

// General Application Routes
Route::view('/', 'pages.welcome');
Route::get('/register-org', OrgRegistrationForm::class)->name('register.org');

Route::middleware(['auth'])->group(function () {
    Route::get('profile', UserProfile::class)->name('profile');

    // App Owner Route
    Route::get('/admin/distribution', DistributionPanel::class)->name('admin.distribution');

    Route::middleware(['role:Admin,Loan Analyst,Vault Manager,Credit Analyst,Collection Specialist'])->group(function () {
        Route::get('dashboard', \App\Livewire\AdminDashboard::class)->middleware('permission:view_dashboard')->name('dashboard');
        Route::get('status-board', \App\Livewire\StatusBoard::class)->middleware('permission:manage_loans')->name('status-board');
        Route::get('loan', \App\Livewire\LoanDashboard::class)->middleware('permission:manage_loans')->name('loan');
        Route::get('loan/pending', \App\Livewire\PendingLoans::class)->middleware('permission:approve_loans')->name('loans.pending');
        Route::view('loan/create', 'pages.loan-application')->middleware('permission:manage_loans')->name('loan.create');
        Route::get('loan/{loan}/edit', function (\App\Models\Loan $loan) {
            return view('pages.loan-application', ['loan' => $loan]);
        })->middleware('permission:manage_loans')->name('loan.edit');
        Route::get('loan/{loan}', \App\Livewire\LoanDetails::class)->middleware('permission:manage_loans')->name('loan.show');
        Route::get('loan/{loan}/print', \App\Livewire\LoanPrint::class)->middleware('permission:manage_loans')->name('loan.print');
        Route::get('loan/{loan}/repayments/print', \App\Livewire\RepaymentPrint::class)->middleware('permission:manage_loans')->name('repayments.print');
        Route::get('loan/{loan}/schedule/print', \App\Livewire\SchedulePrint::class)->middleware('permission:manage_loans')->name('schedule.print');
        Route::get('borrower/{borrower}/loans', \App\Livewire\UserLoans::class)->middleware('permission:manage_loans')->name('borrower.loans');
        Route::get('borrower/{borrower}/profile', \App\Livewire\BorrowerProfile::class)->middleware('permission:manage_borrowers')->name('borrower.profile');
        Route::get('savings/{borrower}', \App\Livewire\SavingsDetails::class)->middleware('permission:manage_borrowers')->name('savings.show');
        Route::get('actions', ActionCenter::class)->middleware('permission:view_dashboard')->name('actions');
        Route::get('reports', \App\Livewire\Reports::class)->middleware('permission:view_reports')->name('reports');
        Route::get('reports/print/{type}', \App\Livewire\GeneralReportPrint::class)->middleware('permission:view_reports')->name('report.print');
        Route::get('collections', \App\Livewire\Collections::class)->middleware('permission:manage_collections')->name('collections');
        Route::get('repayments', \App\Livewire\RepaymentRecords::class)->middleware('permission:manage_collections')->name('repayments.records');
        Route::get('notifications', \App\Livewire\Notifications::class)->name('notifications');
        Route::get('settings', GeneralSettings::class)->middleware('permission:manage_settings')->name('settings');
        Route::get('settings/security', \App\Livewire\Settings\SecuritySettings::class)->name('settings.security');
        Route::get('settings/notifications', \App\Livewire\Settings\NotificationSettings::class)->middleware('permission:manage_settings')->name('settings.notifications');
        Route::get('settings/form-builder', FormBuilder::class)->middleware('permission:manage_settings')->name('settings.form-builder');
        Route::get('settings/roles', RolesManagement::class)->middleware('permission:manage_settings')->name('settings.roles');
        Route::get('settings/team-members', TeamManagement::class)->middleware('permission:manage_settings')->name('settings.team-members');
        Route::view('customer', 'pages.customer')->middleware('permission:manage_borrowers')->name('customer');
        Route::view('customer/create', 'pages.customer-registration')->middleware('permission:manage_borrowers')->name('customer.create');
        Route::get('vault', \App\Livewire\Vault::class)->middleware('permission:manage_vault')->name('vault');
        Route::view('collateral/create', 'pages.add-collateral')->middleware('permission:manage_vault')->name('collateral.create');
    });

    Route::middleware(['role:Borrower'])->group(function () {
        Route::get('borrower/dashboard', BorrowerDashboard::class)->name('borrower.dashboard');
    });
});

require __DIR__.'/auth.php';
