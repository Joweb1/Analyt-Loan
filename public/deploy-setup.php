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
    __DIR__.'/analyt',
    __DIR__.'/../analyt',
];

$analyt_path = null;
foreach ($possible_analyt_paths as $path) {
    if (is_dir($path)) {
        $analyt_path = realpath($path);
        echo "\nFOUND 'analyt' folder at: $analyt_path\n";
        echo "Listing contents of 'analyt':\n";
        print_r(scandir($analyt_path));
        break;
    }
}

if (! $analyt_path) {
    echo "\nERROR: 'analyt' folder not found in any of the searched paths.\n";
    print_r($possible_analyt_paths);
    echo '</pre>';
    exit;
}

$config = [
    'vendor_zip' => $analyt_path.'/vendor.zip',
    'analyt_dir' => $analyt_path,
    'public_dir' => __DIR__, // This is htdocs
    'token' => $_GET['token'] ?? null,
    'expected_token' => 'DEPLOY_TOKEN_PLACEHOLDER',
];

if (! $config['token'] || $config['token'] !== $config['expected_token']) {
    header('HTTP/1.1 403 Forbidden');
    echo "\nAccess Denied. Invalid deployment token.";
    echo '</pre>';
    exit;
}

// 1. Unzip the vendor folder
if (file_exists($config['vendor_zip'])) {
    echo "\nUnzipping vendor.zip into {$config['analyt_dir']}...\n";

    if (! is_writable($config['analyt_dir'])) {
        echo "ERROR: App directory {$config['analyt_dir']} is not writable.\n";
        exit;
    }

    $zip = new ZipArchive;
    if ($zip->open($config['vendor_zip']) === true) {
        $zip->extractTo($config['analyt_dir']);
        $zip->close();
        echo "SUCCESS: Vendor extraction complete.\n";

        echo "\nCleanup: Removing vendor.zip...\n";
        @unlink($config['vendor_zip']);
    } else {
        echo "ERROR: Could not open vendor.zip.\n";
        exit;
    }
} else {
    echo "\nNOTE: vendor.zip not found (it may have been extracted already).\n";
}

// 2. Setup Laravel
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
