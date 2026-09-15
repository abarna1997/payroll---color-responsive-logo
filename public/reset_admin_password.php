<?php

// public/reset_admin_password.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap the Laravel application kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "<h1>Password Reset Utility</h1>";

try {
    $username = 'Prime1-admin';
    $newPassword = 'Admin@2002@22';
    
    // Hash the password securely
    $hashedPassword = Hash::make($newPassword);

    // Update the database
    $updated = DB::table('users')->where('username', $username)->update([
        'password' => $hashedPassword,
    ]);

    if ($updated) {
        echo "<div style='background: green; color: white; padding: 15px; font-size: 18px;'>";
        echo "✅ Password for <strong>{$username}</strong> was successfully updated to your requested password!<br>";
        echo "You can now log in.";
        echo "</div>";
    } else {
        // If 0 rows updated, check if user exists
        $exists = DB::table('users')->where('username', $username)->exists();
        if ($exists) {
            echo "<div style='background: orange; color: white; padding: 15px;'>";
            echo "The password is already set to that exact value, no update was needed!";
            echo "</div>";
        } else {
            echo "<div style='background: red; color: white; padding: 15px;'>";
            echo "❌ User <strong>{$username}</strong> was not found in the live database. Check the spelling.";
            echo "</div>";
        }
    }
} catch (\Exception $e) {
    echo "<div style='background: red; color: white; padding: 15px;'>";
    echo "Error updating password: " . $e->getMessage();
    echo "</div>";
}

echo "<br><br><div style='padding: 10px; background: #eee;'><strong>Important:</strong> Delete this file (<code>reset_admin_password.php</code>) from your server immediately after use for security!</div>";
