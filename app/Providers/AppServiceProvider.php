<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Loan::observe(\App\Observers\LoanObserver::class);
        \App\Models\Borrower::observe(\App\Observers\BorrowerObserver::class);
        \App\Models\Collateral::observe(\App\Observers\CollateralObserver::class);
        \App\Models\Repayment::observe(\App\Observers\RepaymentObserver::class);
        \App\Models\Comment::observe(\App\Observers\CommentObserver::class);
    }
}
