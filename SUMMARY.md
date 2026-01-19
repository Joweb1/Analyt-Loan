# Analyt Loan 2.0 - Development Environment Initialization

## Commands to install the stack:
```bash
composer create-project laravel/laravel analyt-loan-2.0
cd analyt-loan-2.0
composer require livewire/livewire
composer require --dev phpstan/phpstan phpstan/extension-installer
composer require laravel/pennant
php artisan vendor:publish --provider="Laravel\Pennant\PennantServiceProvider"
php artisan migrate
npm install -D tailwindcss postcss autoprefixer
# The following command might fail, if so, create the files manually
npx tailwindcss init -p
```

## `config/auth.php` guard configuration:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'borrower' => [
        'driver' => 'session',
        'provider' => 'borrowers',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    'borrowers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Borrower::class,
    ],
],
```

## Migration Schema Code:

### `create_users_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'collector']);
            $table->uuid('branch_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

### `create_borrowers_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('phone')->unique();
            $table->integer('trust_score')->default(0);
            $table->boolean('portal_access')->default(false);
            $table->string('photo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowers');
    }
};
```

### `create_collaterals_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('borrower_id')->constrained('borrowers')->cascadeOnDelete();
            $table->decimal('market_value', 10, 2);
            $table->enum('status', ['deposited', 'released', 'liquidated']);
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaterals');
    }
};
```

### `create_loans_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount_principal', 10, 2);
            $table->decimal('collateral_coverage', 5, 2);
            $table->enum('status', ['pending_collateral', 'active', 'overdue']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
```

## `LoanEngine.php` service code block:
```php
<?php

namespace App\Services;

use App\Exceptions\CollateralInsufficientException;
use App\Models\Loan;
use App\Models\Collateral;

class LoanEngine
{
    public function activateLoan(Loan $loan)
    {
        $totalCollateralValue = Collateral::where('borrower_id', $loan->borrower_id)
            ->where('status', 'deposited')
            ->sum('market_value');

        if (($totalCollateralValue / $loan->amount_principal) < 0.5) {
            throw new CollateralInsufficientException('Insufficient collateral to activate the loan.');
        }

        $loan->status = 'active';
        $loan->save();

        return $loan;
    }
}
```
