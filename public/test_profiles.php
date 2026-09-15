<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$emp = \App\Models\Employee::where('employee_id', 'P1-1059')->first();
if ($emp) {
    $profiles = \App\Models\SalaryProfile::where('employee_id', $emp->id)->get();
    echo json_encode([
        'employee' => $emp->toArray(),
        'profiles' => $profiles->toArray()
    ], JSON_PRETTY_PRINT);
} else {
    echo "Employee not found.";
}
