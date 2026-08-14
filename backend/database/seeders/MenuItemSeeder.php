<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Nasi Goreng Spesial',
                'category' => 'food',
                'price' => 45000,
                'description' => 'Nasi goreng dengan telor, ayam, dan kerupuk',
                'is_available' => true,
            ],
            [
                'name' => 'Mie Goreng Seafood',
                'category' => 'food',
                'price' => 50000,
                'description' => 'Mie goreng dengan udang dan cumi segar',
                'is_available' => true,
            ],
            [
                'name' => 'Soto Ayam Madura',
                'category' => 'food',
                'price' => 40000,
                'description' => 'Soto ayam dengan kuah gurih berempah',
                'is_available' => true,
            ],
            [
                'name' => 'Es Teh Manis',
                'category' => 'beverage',
                'price' => 15000,
                'description' => 'Es teh manis segar',
                'is_available' => true,
            ],
            [
                'name' => 'Jus Alpukat',
                'category' => 'beverage',
                'price' => 25000,
                'description' => 'Jus alpukat manis dengan kental manis cokelat',
                'is_available' => true,
            ],
            [
                'name' => 'Kopi Hitam Robusta',
                'category' => 'beverage',
                'price' => 20000,
                'description' => 'Kopi hitam hangat aroma mantap',
                'is_available' => true,
            ],
            [
                'name' => 'Kentang Goreng (French Fries)',
                'category' => 'snack',
                'price' => 30000,
                'description' => 'Kentang goreng renyah dengan saus sambal',
                'is_available' => true,
            ],
            [
                'name' => 'Pisang Goreng Keju',
                'category' => 'snack',
                'price' => 28000,
                'description' => 'Pisang goreng hangat dengan parutan keju',
                'is_available' => true,
            ],
            [
                'name' => 'Paket Dinner Romantic',
                'category' => 'package',
                'price' => 350000,
                'description' => 'Paket makan malam romantis untuk 2 orang',
                'is_available' => true,
            ],
            [
                'name' => 'Paket Meeting Break Fast',
                'category' => 'package',
                'price' => 150000,
                'description' => 'Paket prasmanan kopi, teh, dan 3 macam kue',
                'is_available' => true,
            ]
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
