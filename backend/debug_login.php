<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$user = User::whereRaw('LOWER(email) = ?', ['owner@hotel.com'])->first();
if (!$user) {
    echo "ERROR: User owner@hotel.com NOT FOUND\n";
    exit;
}

echo "User ID: " . $user->id . "\n";
echo "Email: " . $user->email . "\n";
echo "Is Active: " . ($user->is_active ? 'YES' : 'NO') . "\n";
echo "DB Password Hash: " . $user->password . "\n";
echo "Hash::check('password'): " . (Hash::check('password', $user->password) ? 'TRUE' : 'FALSE') . "\n";
echo "Auth::attempt(): " . (Auth::attempt(['email' => 'owner@hotel.com', 'password' => 'password']) ? 'SUCCESS' : 'FAILED') . "\n";
