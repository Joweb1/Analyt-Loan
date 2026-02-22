<?php

/**
 * Laravel Deployment Setup Utility for Shared Hosting (cPanel)
 *
 * This script handles unzipping the vendor folder and setting up the environment.
 * SECURITY: Delete this file immediately after use.
 */

// Debugging: Let's see where we are and what's around us
echo '<h1>Analyt Loan Deployment Debugger</h1>';
echo '<pre>';
echo 'Current Directory: '.__DIR__."\n";
echo 'Parent Directory: '.realpath(__DIR__.'/..')."\n\n";

echo "Listing Current Directory (__DIR__):\n";
print_r(scandir(__DIR__));

// Try to find the project core folder
$possible_core_paths = [
    realpath(__DIR__.'/..'), // Standard: core is parent of public/
    __DIR__.'/automation',
    __DIR__.'/../automation',
    __DIR__.'/analyt',
    __DIR__.'/../analyt',
];

$core_path = null;
foreach ($possible_core_paths as $path) {
    if ($path && is_dir($path) && file_exists($path.'/bootstrap/app.php')) {
        $core_path = realpath($path);
        echo "\nFOUND project core at: $core_path\n";
        break;
    }
}

if (! $core_path) {
    echo "\nERROR: Project core folder not found in any of the searched paths.\n";
    print_r($possible_core_paths);
    echo "Current contents of current dir (__DIR__):\n";
    print_r(scandir(__DIR__));
    echo '</pre>';
    exit;
}

$config = [
    'core_dir' => $core_path,
    'public_dir' => __DIR__, // This is where the script is located
    'token' => $_GET['token'] ?? null,
    'expected_token' => 'DEPLOY_TOKEN_PLACEHOLDER',
];

if (! $config['token'] || $config['token'] !== $config['expected_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo "\nAccess Denied. Invalid deployment token.";
    // More detailed debug info for token mismatch
    $placeholder_replaced = (strpos($config['expected_token'], 'PLACEHOLDER') === false);
    echo "\nReceived token: ".($config['token'] ? 'PROVIDED (Length: '.strlen($config['token']).')' : 'EMPTY');
    echo "\nExpected token state: ".($placeholder_replaced ? 'REPLACED WITH SECRET' : 'STILL PLACEHOLDER (GITHUB SECRET NOT PASSED)');
    echo '</pre>';
    exit;
}

// 0. Environment Check
echo "\nChecking for .env file...\n";
if (! file_exists($config['core_dir'].'/.env')) {
    echo "WARNING: .env file NOT FOUND in project core ({$config['core_dir']}).\n";
    if (file_exists($config['core_dir'].'/.env.example')) {
        echo "Found .env.example, using it as fallback (WARNING: DB config may be incorrect).\n";
        copy($config['core_dir'].'/.env.example', $config['core_dir'].'/.env');
    }
} else {
    echo "SUCCESS: .env file located.\n";
}

// 1. Setup Laravel
echo "\nBootstrapping Laravel for final setup...\n";
$autoload = $config['core_dir'].'/vendor/autoload.php';
$bootstrap = $config['core_dir'].'/bootstrap/app.php';

if (file_exists($autoload) && file_exists($bootstrap)) {
    require $autoload;
    $app = require_once $bootstrap;

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "Running Migrations...\n";
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
    } catch (\Exception $e) {
        echo 'Migration failed: '.$e->getMessage()."\n";
    }

    echo "Creating Storage Symlink...\n";
    $target = $config['core_dir'].'/storage/app/public';
    // If we are in public subfolder, the link should be in current dir
    // If we are in htdocs, the link should be in current dir
    $link = $config['public_dir'].'/storage';
    if (! file_exists($link)) {
        if (symlink($target, $link)) {
            echo "SUCCESS: Storage symlink created.\n";
        } else {
            echo "WARNING: Could not create symlink.\n";
        }
    }

    echo "Clearing Caches...\n";
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo \Illuminate\Support\Facades\Artisan::output();
} else {
    echo "ERROR: Laravel bootstrap files not found in {$config['core_dir']}.\n";
}

echo '</pre>';
echo '<h2>Deployment Complete!</h2>';
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file immediately!</strong></p>";
