<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HotelBranch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HotelBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Grand Hotel Merdeka Jakarta',
                'slug' => 'jakarta',
                'address' => 'Jl. Jendral Sudirman No. 45, Jakarta Pusat',
                'city' => 'Jakarta',
                'phone' => '021-5550123',
                'email' => 'jakarta@grandhotelmerdeka.com',
                'description' => 'Cabang utama kami di pusat bisnis kota Jakarta dengan fasilitas bintang 5.',
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ],
            [
                'name' => 'Grand Hotel Merdeka Bali',
                'slug' => 'bali',
                'address' => 'Jl. Pantai Kuta No. 88, Badung, Bali',
                'city' => 'Bali',
                'phone' => '0361-7770456',
                'email' => 'bali@grandhotelmerdeka.com',
                'description' => 'Resort kemewahan di pinggir pantai Kuta Bali dengan pemandangan sunset memukau.',
                'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ],
            [
                'name' => 'Grand Hotel Merdeka Surabaya',
                'slug' => 'surabaya',
                'address' => 'Jl. Tunjungan No. 12, Surabaya',
                'city' => 'Surabaya',
                'phone' => '031-4440789',
                'email' => 'surabaya@grandhotelmerdeka.com',
                'description' => 'Hotel bisnis strategis di jantung kota Surabaya dengan fasilitas konvensi modern.',
                'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $b) {
            HotelBranch::updateOrCreate(['slug' => $b['slug']], $b);
        }

        $defaultBranch = HotelBranch::where('slug', 'jakarta')->first();
        if ($defaultBranch) {
            // Update orphan records to default branch
            $tables = [
                'room_types',
                'rooms',
                'halls',
                'bookings',
                'hall_bookings',
                'housekeeping_tasks',
                'restaurant_orders',
                'laundry_orders',
                'settings',
            ];

            foreach ($tables as $t) {
                if (DB::getSchemaBuilder()->hasTable($t) && DB::getSchemaBuilder()->hasColumn($t, 'hotel_branch_id')) {
                    DB::table($t)->whereNull('hotel_branch_id')->update(['hotel_branch_id' => $defaultBranch->id]);
                }
            }

            // Assign all users to default branch if not already assigned
            $users = User::all();
            foreach ($users as $u) {
                if (!$u->hotelBranches()->where('hotel_branch_id', $defaultBranch->id)->exists()) {
                    $u->hotelBranches()->attach($defaultBranch->id);
                }
            }
        }
    }
}
