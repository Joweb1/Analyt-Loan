<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.welcome');

Route::view('dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'pages.profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('status-board', 'pages.status-board')
        ->middleware(['auth'])
        ->name('status-board');

Route::view('loan', 'pages.loan')
    ->middleware(['auth'])
    ->name('loan');

Route::view('loan/create', 'pages.loan-application')
    ->middleware(['auth'])
    ->name('loan.create');

Route::view('collections', 'pages.collections')
    ->middleware(['auth'])
    ->name('collections');

Route::view('settings', 'pages.settings')
    ->middleware(['auth'])
    ->name('settings');

Route::view('settings/team-members', 'pages.team-members')
    ->middleware(['auth'])
    ->name('settings.team-members');

Route::view('customer', 'pages.customer')
    ->middleware(['auth'])
    ->name('customer');

Route::view('customer/create', 'pages.customer-registration')
    ->middleware(['auth'])
    ->name('customer.create');

Route::view('vault', 'pages.vault')
    ->middleware(['auth'])
    ->name('vault');

Route::view('collateral/create', 'pages.add-collateral')
    ->middleware(['auth'])
    ->name('collateral.create');

require __DIR__.'/auth.php';
