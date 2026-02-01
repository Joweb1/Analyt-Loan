<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BorrowerList;

Route::get('/borrowers', BorrowerList::class)->name('borrowers.index');

// General Application Routes
Route::view('/', 'pages.welcome');

Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'pages.profile')->name('profile');

    Route::middleware(['role:Admin,Loan Analyst,Vault Manager,Credit Analyst,Collection Specialist'])->group(function () {
        Route::view('dashboard', 'pages.dashboard')->name('dashboard');
        Route::view('status-board', 'pages.status-board')->name('status-board');
        Route::view('loan', 'pages.loan')->name('loan');
        Route::view('loan/create', 'pages.loan-application')->name('loan.create');
        Route::view('collections', 'pages.collections')->name('collections');
        Route::view('settings', 'pages.settings')->name('settings');
        Route::view('settings/team-members', 'pages.team-members')->name('settings.team-members');
        Route::view('customer', 'pages.customer')->name('customer');
        Route::view('customer/create', 'pages.customer-registration')->name('customer.create');
        Route::view('vault', 'pages.vault')->name('vault');
        Route::view('collateral/create', 'pages.add-collateral')->name('collateral.create');
    });

    Route::middleware(['role:Borrower'])->group(function () {
        Route::view('borrower/dashboard', 'pages.borrower-dashboard')->name('borrower.dashboard');
    });
});


require __DIR__.'/auth.php';
