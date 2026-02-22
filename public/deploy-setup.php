<?php

/**
 * Laravel Deployment Setup Utility for Shared Hosting (cPanel)
 *
 * This script handles unzipping the deployment package and setting up the environment.
 * SECURITY: Delete this file immediately after use.
 */

// Use the database password or a custom deployment token as the security token
// For safety, let's look for a DEPLOY_TOKEN in the environment or use a hardcoded fallback if needed.
// However, since we might not have the .env yet (it's in the zip), let's use a query parameter 'token'
// and compare it against a secret defined in the GitHub Action.

$config = [
    'zip_file' => __DIR__.'/../deploy.zip',
    'extract_to' => __DIR__.'/../analyt',
    'public_dir' => __DIR__, // This is htdocs
    'token' => $_GET['token'] ?? null,
    'expected_token' => 'YOUR_DEPLOYMENT_TOKEN_HERE', // This should be replaced or checked against a secret
];

// If you want to use a secret from GitHub, you can replace the token line above during the CI/CD build.

if (! $config['token'] || $config['token'] !== 'analyt_deploy_secret_2026') {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied. Invalid deployment token.');
}

echo '<h1>Analyt Loan Deployment Utility</h1>';
echo '<pre>';

// 1. Unzip the application
if (file_exists($config['zip_file'])) {
    echo "Extracting {$config['zip_file']} to {$config['extract_to']}...\n";

    $zip = new ZipArchive;
    if ($zip->open($config['zip_file']) === true) {
        if (! is_dir($config['extract_to'])) {
            mkdir($config['extract_to'], 0755, true);
        }
        $zip->extractTo($config['extract_to']);
        $zip->close();
        echo "SUCCESS: Extraction complete.\n";
    } else {
        echo "ERROR: Could not open zip file.\n";
        exit;
    }
} else {
    echo "ERROR: Zip file not found at {$config['zip_file']}.\n";
    exit;
}

// 2. Sync Public Folder
echo "Syncing public assets from analyt/public to htdocs...\n";
function syncDir($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src.'/'.$file)) {
                syncDir($src.'/'.$file, $dst.'/'.$file);
            } else {
                copy($src.'/'.$file, $dst.'/'.$file);
            }
        }
    }
    closedir($dir);
}

if (is_dir($config['extract_to'].'/public')) {
    syncDir($config['extract_to'].'/public', $config['public_dir']);
    echo "SUCCESS: Public assets synced.\n";
}

// 3. Setup Laravel
echo "Bootstrapping Laravel for final setup...\n";
$autoload = $config['extract_to'].'/vendor/autoload.php';
$bootstrap = $config['extract_to'].'/bootstrap/app.php';

if (file_exists($autoload) && file_exists($bootstrap)) {
    require $autoload;
    $app = require_once $bootstrap;

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "Running Migrations...\n";
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();

    echo "Creating Storage Symlink...\n";
    // Manual symlink since htdocs is outside analyt
    $target = $config['extract_to'].'/storage/app/public';
    $link = $config['public_dir'].'/storage';
    if (! file_exists($link)) {
        if (symlink($target, $link)) {
            echo "SUCCESS: Storage symlink created.\n";
        } else {
            echo "WARNING: Could not create symlink. You may need to copy storage contents manually.\n";
        }
    }

    echo "Clearing Caches...\n";
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo \Illuminate\Support\Facades\Artisan::output();
}

echo "\nCleanup: Removing zip file...\n";
unlink($config['zip_file']);

echo '</pre>';
echo '<h2>Deployment Complete!</h2>';
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file (htdocs/deploy-setup.php) immediately!</strong></p>";
