<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        $frontOfficeRole = Role::where('name', 'front_office')->first();
        $housekeepingRole = Role::where('name', 'housekeeping')->first();

        User::updateOrCreate(['email' => 'owner@hotel.com'], [
            'role_id' => $ownerRole->id,
            'name' => 'Admin Owner',
            'email' => 'owner@hotel.com',
            'phone' => '081234567890',
            'password' => 'password',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'frontdesk@hotel.com'], [
            'role_id' => $frontOfficeRole->id,
            'name' => 'Front Desk',
            'email' => 'frontdesk@hotel.com',
            'phone' => '081234567891',
            'password' => 'password',
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'housekeeping@hotel.com'], [
            'role_id' => $housekeepingRole->id,
            'name' => 'Housekeeping Staff',
            'email' => 'housekeeping@hotel.com',
            'phone' => '081234567892',
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
