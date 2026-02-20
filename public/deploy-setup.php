<?php

/**
 * Laravel Deployment Setup Utility for Shared Hosting (cPanel)
 * 
 * Since SSH access is not available, this script allows you to run
 * necessary artisan commands via the web browser.
 * 
 * SECURITY: Delete this file immediately after use.
 */

// Define a simple security token. Change this or pass it as ?token=...
$secretToken = 'setup_'.substr(md5(date('Y-m-d')), 0, 8);

if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied. Provide the correct token. (Hint: The current daily token is: $secretToken)");
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

// Create a kernel to handle the request
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<h1>Laravel Deployment Setup</h1>";
echo "<pre>";

function runCommand($command, $params = []) {
    echo "Executing: php artisan $command " . json_encode($params) . "
";
    try {
        Artisan::call($command, $params);
        echo Artisan::output() . "
";
        echo "SUCCESS
";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "
";
    }
    echo str_repeat('-', 40) . "
";
}

// 1. Run Migrations
runCommand('migrate', ['--force' => true]);

// 2. Create Storage Symbolic Link
// On shared hosting, you might need to use absolute paths if the default fails.
runCommand('storage:link');

// 3. Optimize (Caches config, routes, views)
runCommand('optimize');

// 4. Custom: Create Symlink manually if storage:link fails (Optional)
/*
$publicStorage = __DIR__ . '/storage';
$appStorage = __DIR__ . '/../storage/app/public';
if (!file_exists($publicStorage)) {
    if (symlink($appStorage, $publicStorage)) {
        echo "Manually created storage symlink.
";
    }
}
*/

echo "</pre>";
echo "<h2>Setup Finished.</h2>";
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file (public/deploy-setup.php) immediately!</strong></p>";
