<?php
echo "<h1>PHP Image Processing Support</h1>";

echo "<h2>GD Extension</h2>";
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "<p style='color:green;font-weight:bold;'>GD is ENABLED</p>";
    $gd = gd_info();
    echo "<pre>";
    print_r($gd);
    echo "</pre>";
} else {
    echo "<p style='color:red;font-weight:bold;'>GD is MISSING or DISABLED</p>";
}

echo "<h2>Imagick Extension</h2>";
if (extension_loaded('imagick') && class_exists('Imagick')) {
    echo "<p style='color:green;font-weight:bold;'>Imagick is ENABLED</p>";
} else {
    echo "<p style='color:red;font-weight:bold;'>Imagick is MISSING or DISABLED</p>";
}

echo "<h2>System Info</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
