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

echo "\nListing Parent Directory (__DIR__/..):\n";
if (is_dir(__DIR__.'/..')) {
    print_r(scandir(__DIR__.'/..'));
} else {
    echo "Parent directory not readable.\n";
}

// Try to find the 'analyt' folder
$possible_analyt_paths = [
    __DIR__.'/analyt',      // Parallel to analyt folder (htdocs root)
    __DIR__.'/../analyt',   // One level up from analyt folder (if in a subfolder)
    realpath(__DIR__.'/..'), // Parent directory (if we are inside analyt/public)
];

$analyt_path = null;
foreach ($possible_analyt_paths as $path) {
    if (is_dir($path) && file_exists($path.'/bootstrap/app.php')) {
        $analyt_path = realpath($path);
        echo "\nFOUND project core at: $analyt_path\n";
        break;
    }
}

if (! $analyt_path) {
    echo "\nERROR: 'analyt' folder (project core) not found in any of the searched paths.\n";
    print_r($possible_analyt_paths);
    echo "Current contents of current dir (__DIR__):\n";
    print_r(scandir(__DIR__));
    echo '</pre>';
    exit;
}

$config = [
    'analyt_dir' => $analyt_path,
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
if (! file_exists($config['analyt_dir'].'/.env')) {
    echo "WARNING: .env file NOT FOUND in project core ({$config['analyt_dir']}).\n";
    if (file_exists($config['analyt_dir'].'/.env.example')) {
        echo "Found .env.example, using it as fallback (WARNING: DB config may be incorrect).\n";
        copy($config['analyt_dir'].'/.env.example', $config['analyt_dir'].'/.env');
    }
} else {
    echo "SUCCESS: .env file located.\n";
}

// 1. Setup Laravel
echo "\nBootstrapping Laravel for final setup...\n";
$autoload = $config['analyt_dir'].'/vendor/autoload.php';
$bootstrap = $config['analyt_dir'].'/bootstrap/app.php';

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
    $target = $config['analyt_dir'].'/storage/app/public';
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
    echo "ERROR: Laravel bootstrap files not found in {$config['analyt_dir']}.\n";
}

echo '</pre>';
echo '<h2>Deployment Complete!</h2>';
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file (htdocs/deploy-setup.php) immediately!</strong></p>";
