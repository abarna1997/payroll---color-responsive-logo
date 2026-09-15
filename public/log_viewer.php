<?php
// Clear OPcache just in case the server is aggressively caching old PHP files
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<div style='background: green; color: white; padding: 10px;'>OPcache successfully cleared!</div>";
}

// Clear apcu if available
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
}

$logFile = __DIR__ . '/../storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die("Log file not found at: $logFile");
}

$lines = file($logFile);
$lastLines = array_slice($lines, -100);

echo "<h1>Latest 100 Lines of laravel.log</h1>";
echo "<pre style='background: #111; color: #0f0; padding: 20px; overflow-x: auto;'>";
foreach ($lastLines as $line) {
    echo htmlspecialchars($line);
}
echo "</pre>";
