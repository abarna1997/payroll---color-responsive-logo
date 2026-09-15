<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

// Get all devices
$devices = \App\Models\Device::all();

foreach ($devices as $device) {
    \App\Models\DeviceCommand::create([
        'device_id' => $device->id,
        'command' => 'CLEAR DATA',
        'status' => 'pending'
    ]);
}

echo "WIPE COMMAND (CLEAR DATA) QUEUED FOR ALL DEVICES!<br>";
echo "The devices will wipe themselves within the next 10 seconds.";
