<?php

/**
 * Laravel Deployment Setup Utility for Shared Hosting (cPanel)
 *
 * This script handles unzipping the deployment package and setting up the environment.
 * SECURITY: Delete this file immediately after use.
 */
$possible_paths = [
    __DIR__.'/deploy.zip',
    __DIR__.'/../deploy.zip',
    dirname(__DIR__).'/deploy.zip',
];

$zip_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $zip_path = $path;
        break;
    }
}

$config = [
    'zip_file' => $zip_path,
    'extract_to' => __DIR__.'/../analyt',
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

// 1. Unzip the application
if ($config['zip_file'] && file_exists($config['zip_file'])) {
    echo "Zip found at: {$config['zip_file']}\n";
    echo "Extracting to: {$config['extract_to']}\n";

    if (! is_dir($config['extract_to'])) {
        if (! mkdir($config['extract_to'], 0755, true)) {
            echo "ERROR: Could not create extraction directory {$config['extract_to']}.\n";
            echo 'Parent directory is: '.dirname($config['extract_to'])."\n";
            echo 'Is parent writable? '.(is_writable(dirname($config['extract_to'])) ? 'YES' : 'NO')."\n";
            exit;
        }
    }

    if (! is_writable($config['extract_to'])) {
        echo "ERROR: Extraction directory {$config['extract_to']} is not writable.\n";
        exit;
    }

    $zip = new ZipArchive;
    if ($zip->open($config['zip_file']) === true) {
        $zip->extractTo($config['extract_to']);
        $zip->close();
        echo "SUCCESS: Extraction complete.\n";
    } else {
        echo "ERROR: Could not open zip file.\n";
        exit;
    }
} else {
    echo "ERROR: Zip file not found.\n";
    echo 'Current Directory: '.__DIR__."\n";
    echo "Searched paths:\n";
    print_r($possible_paths);
    echo "\nDirectory listing of ".__DIR__.":\n";
    print_r(scandir(__DIR__));
    exit;
}

// 2. Sync Public Folder
echo "\nSyncing public assets from analyt/public to htdocs...\n";
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
echo "\nBootstrapping Laravel for final setup...\n";
$autoload = $config['extract_to'].'/vendor/autoload.php';
$bootstrap = $config['extract_to'].'/bootstrap/app.php';

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
    $target = $config['extract_to'].'/storage/app/public';
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
    echo "ERROR: Laravel files not found in extraction directory. Check extraction logs above.\n";
}

echo "\nCleanup: Removing zip file...\n";
@unlink($config['zip_file']);

echo '</pre>';
echo '<h2>Deployment Complete!</h2>';
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file (htdocs/deploy-setup.php) immediately!</strong></p>";
