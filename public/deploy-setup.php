<?php

/**
 * Laravel Deployment Setup Utility for Shared Hosting (cPanel)
 *
 * This script handles unzipping the vendor folder and setting up the environment.
 * SECURITY: Delete this file immediately after use.
 */
$config = [
    'vendor_zip' => __DIR__.'/../analyt/vendor.zip',
    'analyt_dir' => __DIR__.'/../analyt',
    'public_dir' => __DIR__, // This is htdocs
    'token' => $_GET['token'] ?? null,
    'expected_token' => 'DEPLOY_TOKEN_PLACEHOLDER',
];

if (! $config['token'] || $config['token'] !== $config['expected_token']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied. Invalid deployment token.');
}

echo '<h1>Analyt Loan Deployment Utility</h1>';
echo '<pre>';

// 1. Unzip the vendor folder
if (file_exists($config['vendor_zip'])) {
    echo "Unzipping vendor.zip into {$config['analyt_dir']}...\n";

    if (! is_writable($config['analyt_dir'])) {
        echo "ERROR: App directory {$config['analyt_dir']} is not writable.\n";
        exit;
    }

    $zip = new ZipArchive;
    if ($zip->open($config['vendor_zip']) === true) {
        // Extract to analyt folder
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
    echo "NOTE: vendor.zip not found (it may have been extracted already).\n";
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
            echo "WARNING: Could not create symlink. Using absolute paths might help.\n";
        }
    }

    echo "Clearing Caches...\n";
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo \Illuminate\Support\Facades\Artisan::output();
} else {
    echo "ERROR: Laravel bootstrap files not found. Check if files were uploaded correctly to: {$config['analyt_dir']}\n";
}

echo '</pre>';
echo '<h2>Deployment Complete!</h2>';
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file (htdocs/deploy-setup.php) immediately!</strong></p>";
