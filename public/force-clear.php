<?php
// force-clear.php
// A hard-coded script to violently delete all cached view files and OPcache

$viewPath = __DIR__ . '/../storage/framework/views';
$success = true;

if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*'); 
    foreach ($files as $file) { 
        if (is_file($file) && basename($file) !== '.gitignore') {
            if (!unlink($file)) {
                $success = false;
                echo "Failed to delete: $file <br>";
            }
        }
    }
}

if (function_exists('opcache_reset')) {
    opcache_reset();
}

if ($success) {
    echo "<h2 style='color:green;'>SUCCESS: All Laravel cached views have been manually nuked.</h2>";
    echo "<p>Please close this page, go back to your Employee Directory, and hard refresh (Ctrl + F5).</p>";
} else {
    echo "<h2 style='color:red;'>FAILED: The web server does not have permission to delete the cached views.</h2>";
}
