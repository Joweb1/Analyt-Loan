<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\TenantSession::class);
        $this->app->singleton(\App\Contracts\StorageProvider::class, \App\Services\Storage\LaravelStorageProvider::class);

        if (config('app.is_production')) {
            // In the Docker structure, the code is in /var/www/laravel-app
            // and the public files are in /var/www/html
            $this->app->usePublicPath(realpath(base_path('../html')));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.is_production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Define Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('financial_ops', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

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

        Event::listen(
            \App\Events\LoanRepaymentReceived::class,
            \App\Listeners\RecalculateTrustScore::class,
        );

        Event::listen(
            \App\Events\LoanRepaymentReceived::class,
            \App\Listeners\SyncLoanSchedule::class,
        );

        Event::listen(
            \App\Events\LoanRepaymentReceived::class,
            \App\Listeners\UpdateBorrowerReadModel::class,
        );
    }
}
