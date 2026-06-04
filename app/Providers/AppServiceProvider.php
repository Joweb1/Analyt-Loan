<?php

namespace App\Providers;

use App\Contracts\StorageProvider;
use App\Events\LoanRepaymentReceived;
use App\Listeners\RecalculateTrustScore;
use App\Listeners\SyncLoanSchedule;
use App\Listeners\UpdateBorrowerReadModel;
use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Comment;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\PaymentProof;
use App\Models\Repayment;
use App\Models\SystemNotification;
use App\Models\User;
use App\Observers\BorrowerObserver;
use App\Observers\CollateralObserver;
use App\Observers\CommentObserver;
use App\Observers\LoanObserver;
use App\Observers\OrganizationObserver;
use App\Observers\PaymentProofObserver;
use App\Observers\RepaymentObserver;
use App\Observers\SystemNotificationObserver;
use App\Observers\UserObserver;
use App\Services\Storage\LaravelStorageProvider;
use App\Services\TenantSession;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
        $this->app->singleton(TenantSession::class);
        $this->app->singleton(StorageProvider::class, LaravelStorageProvider::class);

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
        Blade::directive('fetch', function ($expression) {
            return "<?php echo fetch_data($expression); ?>";
        });

        if ($this->app->isProduction() || config('app.is_production')) {
            URL::forceScheme('https');

            if (config('app.url')) {
                URL::forceRootUrl(config('app.url'));
            }

            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $_SERVER['HTTPS'] = 'on';
                $_SERVER['SERVER_PORT'] = 443;
            }

            // Explicitly set domain to null for host-only cookies
            config(['session.domain' => null]);
        }
        // Define Rate Limiters
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('financial_ops', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Implicitly grant "Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') || $user->email === config('app.owner') ? true : null;
        });

        Loan::observe(LoanObserver::class);
        Borrower::observe(BorrowerObserver::class);
        Collateral::observe(CollateralObserver::class);
        Repayment::observe(RepaymentObserver::class);
        Comment::observe(CommentObserver::class);
        SystemNotification::observe(SystemNotificationObserver::class);
        Organization::observe(OrganizationObserver::class);
        User::observe(UserObserver::class);
        PaymentProof::observe(PaymentProofObserver::class);

        Event::listen(
            LoanRepaymentReceived::class,
            RecalculateTrustScore::class,
        );

        Event::listen(
            LoanRepaymentReceived::class,
            SyncLoanSchedule::class,
        );

        Event::listen(
            LoanRepaymentReceived::class,
            UpdateBorrowerReadModel::class,
        );
    }
}
