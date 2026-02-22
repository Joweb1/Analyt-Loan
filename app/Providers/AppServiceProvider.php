<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('app.is_production')) {
            // If analyt is inside htdocs, the public path is the parent directory
            $this->app->usePublicPath(base_path('../'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') || $user->email === config('app.owner') ? true : null;
        });

        \App\Models\Loan::observe(\App\Observers\LoanObserver::class);
        \App\Models\Borrower::observe(\App\Observers\BorrowerObserver::class);
        \App\Models\Collateral::observe(\App\Observers\CollateralObserver::class);
        \App\Models\Repayment::observe(\App\Observers\RepaymentObserver::class);
        \App\Models\Comment::observe(\App\Observers\CommentObserver::class);
        \App\Models\SystemNotification::observe(\App\Observers\SystemNotificationObserver::class);
        \App\Models\Organization::observe(\App\Observers\OrganizationObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\PaymentProof::observe(\App\Observers\PaymentProofObserver::class);
    }
}
