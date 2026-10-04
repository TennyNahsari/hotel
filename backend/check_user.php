<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'owner@hotel.com')->first();
if ($user) {
    echo "USER_FOUND\n";
    echo "Email: " . $user->email . "\n";
    echo "Is Active: " . ($user->is_active ? '1' : '0') . "\n";
    echo "Hash: " . $user->password . "\n";
    echo "Check 'password': " . (Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'MATCH' : 'MISMATCH') . "\n";
} else {
    echo "USER_NOT_FOUND\n";
}
