<?php
// clear-cache.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "<h1>Clearing Laravel Cache and OPcache</h1>";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color: green;'>&#10003; OPcache cleared successfully! (New PHP files will be loaded)</p>";
} else {
    echo "<p style='color: orange;'>OPcache is not enabled on this server.</p>";
}

try {
    // Clear views
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "<p>&#10003; Views cache cleared successfully!</p>";

    // Clear application cache
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "<p>&#10003; Application cache cleared successfully!</p>";

    // Clear config
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "<p>&#10003; Config cache cleared successfully!</p>";

    // Clear route cache
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    echo "<p>&#10003; Route cache cleared successfully!</p>";

    echo "<br><strong style='color: green;'>All caches successfully cleared! You can now close this page and hard refresh your web app (Ctrl + Shift + R).</strong>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
