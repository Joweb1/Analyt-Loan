<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.welcome');

Route::view('dashboard', 'pages.dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'pages.profile')
    ->middleware(['auth'])
    ->name('profile');
    
Route::view('loan', 'pages.loan')
    ->middleware(['auth'])
    ->name('loan');

require __DIR__.'/auth.php';
