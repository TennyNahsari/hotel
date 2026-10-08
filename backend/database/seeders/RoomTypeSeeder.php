<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\HotelBranch;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Hotel Branches exist
        $branches = [
            'jakarta' => HotelBranch::where('slug', 'jakarta')->first() ?: HotelBranch::firstOrCreate(['slug' => 'jakarta'], [
                'name' => 'Grand Hotel Merdeka Jakarta',
                'address' => 'Jl. Jendral Sudirman No. 45, Jakarta Pusat',
                'city' => 'Jakarta',
                'phone' => '021-5550123',
                'email' => 'jakarta@grandhotelmerdeka.com',
                'description' => 'Cabang utama kami di pusat bisnis kota Jakarta dengan fasilitas bintang 5.',
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ]),
            'bali' => HotelBranch::where('slug', 'bali')->first() ?: HotelBranch::firstOrCreate(['slug' => 'bali'], [
                'name' => 'Grand Hotel Merdeka Bali',
                'address' => 'Jl. Pantai Kuta No. 88, Badung, Bali',
                'city' => 'Bali',
                'phone' => '0361-7770456',
                'email' => 'bali@grandhotelmerdeka.com',
                'description' => 'Resort kemewahan di pinggir pantai Kuta Bali dengan pemandangan sunset memukau.',
                'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ]),
            'surabaya' => HotelBranch::where('slug', 'surabaya')->first() ?: HotelBranch::firstOrCreate(['slug' => 'surabaya'], [
                'name' => 'Grand Hotel Merdeka Surabaya',
                'address' => 'Jl. Tunjungan No. 12, Surabaya',
                'city' => 'Surabaya',
                'phone' => '031-4440789',
                'email' => 'surabaya@grandhotelmerdeka.com',
                'description' => 'Hotel bisnis strategis di jantung kota Surabaya dengan fasilitas konvensi modern.',
                'image' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ]),
        ];

        // Seed Room Types & Rooms per Branch
        $branchData = [
            'jakarta' => [
                [
                    'type' => [
                        'name' => 'Standard Merdeka',
                        'description' => 'Kamar standar nyaman dengan pemandangan kota Jakarta dan fasilitas bisnis lengkap.',
                        'base_price' => 450000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 43"', 'High Speed WiFi', 'Shower Hot Water', 'Work Desk', 'Tea/Coffee Maker'],
                        'image_url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'J101', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'J102', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'J103', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'J104', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'J105', 'floor' => '1', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Deluxe Business',
                        'description' => 'Kamar Deluxe mewah yang ideal untuk pebisnis dengan meja kerja luas dan bathtub.',
                        'base_price' => 750000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 50"', 'High Speed WiFi', 'Bathtub', 'Mini Bar', 'City View', 'Work Desk & Ergonomic Chair', 'Safe Deposit Box'],
                        'image_url' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'J201', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'J202', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'J203', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'J204', 'floor' => '2', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Executive Suite',
                        'description' => 'Suite luas dilengkapi ruang tamu terpisah, Jacuzzi privat, dan pemandangan Sudirman.',
                        'base_price' => 1350000,
                        'capacity' => 3,
                        'facilities' => ['AC', 'Smart TV 55"', 'High Speed WiFi', 'Bathtub & Jacuzzi', 'Mini Bar', 'Panoramic City View', 'Separate Living Room', 'Espresso Machine', 'Breakfast Included'],
                        'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'J301', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'J302', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'J303', 'floor' => '3', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Presidential Suite Merdeka',
                        'description' => 'Kamar termewah untuk tamu VIP dengan Jacuzzi privat, ruang makan, dan layanan butler 24 jam.',
                        'base_price' => 2500000,
                        'capacity' => 4,
                        'facilities' => ['AC', 'Smart TV 65"', 'High Speed WiFi', 'Private Jacuzzi', 'Full Mini Bar', 'Executive Lounge Access', 'Master Bedroom & Living Room', 'Dining Table', '24h Butler Service'],
                        'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'J401', 'floor' => '4', 'status' => 'available'],
                    ],
                ],
            ],
            'bali' => [
                [
                    'type' => [
                        'name' => 'Superior Beachfront',
                        'description' => 'Kamar tropis modern dengan balkon pribadi menghadap taman tropis Kuta Bali.',
                        'base_price' => 650000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 43"', 'High Speed WiFi', 'Rain Shower', 'Private Balcony', 'Tropical Garden View', 'Tea/Coffee Maker'],
                        'image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'B101', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'B102', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'B103', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'B104', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'B105', 'floor' => '1', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Deluxe Ocean View Villa',
                        'description' => 'Villa Deluxe dengan akses langsung ke pantai dan bathtub berlatar laut Kuta.',
                        'base_price' => 1100000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 50"', 'High Speed WiFi', 'Bathtub with Ocean View', 'Mini Bar', 'Direct Beach Access', 'Daybed Terrace', 'Espresso Machine'],
                        'image_url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'B201', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'B202', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'B203', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'B204', 'floor' => '2', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Ocean Suite Kuta',
                        'description' => 'Suite mewah dengan kolam renang privat plunge pool dan pemandangan sunset memukau.',
                        'base_price' => 1850000,
                        'capacity' => 3,
                        'facilities' => ['AC', 'Smart TV 55"', 'High Speed WiFi', 'Freestanding Bathtub', 'Private Plunge Pool', 'Panoramic Sunset View', 'Living Room', 'Daily Cocktail'],
                        'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'B301', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'B302', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'B303', 'floor' => '3', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Grand Sunset Pool Villa',
                        'description' => 'Villa 2 kamar terbaik di Bali dengan kolam infinity privat dan layanan chef pribadi.',
                        'base_price' => 3200000,
                        'capacity' => 4,
                        'facilities' => ['AC', 'Smart TV 65"', 'High Speed WiFi', 'Private Infinity Pool', 'Gazebo & Sunbed', 'Full Ocean Sunset View', '2 Bedrooms', 'Private Chef Service'],
                        'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'B401', 'floor' => '4', 'status' => 'available'],
                    ],
                ],
            ],
            'surabaya' => [
                [
                    'type' => [
                        'name' => 'Superior City View',
                        'description' => 'Kamar superior modern di pusat kota Surabaya dengan meja kerja dan internet cepat.',
                        'base_price' => 400000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 43"', 'High Speed WiFi', 'Standing Shower', 'Work Station', 'Tea/Coffee Maker'],
                        'image_url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'S101', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'S102', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'S103', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'S104', 'floor' => '1', 'status' => 'available'],
                        ['number' => 'S105', 'floor' => '1', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Deluxe Tunjungan',
                        'description' => 'Kamar Deluxe bernuansa elegan dengan pemandangan kawasan bersejarah Jalan Tunjungan.',
                        'base_price' => 680000,
                        'capacity' => 2,
                        'facilities' => ['AC', 'Smart TV 50"', 'High Speed WiFi', 'Bathtub', 'Mini Bar', 'Historical City View', 'Ergonomic Desk'],
                        'image_url' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'S201', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'S202', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'S203', 'floor' => '2', 'status' => 'available'],
                        ['number' => 'S204', 'floor' => '2', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Executive Business Suite',
                        'description' => 'Suite bisnis lengkap dengan meja rapat mini, ruang tamu terpisah, dan layanan laundry gratis.',
                        'base_price' => 1200000,
                        'capacity' => 3,
                        'facilities' => ['AC', 'Smart TV 55"', 'High Speed WiFi', 'Bathtub', 'Mini Bar', 'Living Room', 'Meeting Table', 'Free Laundry 2 Pcs/day'],
                        'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'S301', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'S302', 'floor' => '3', 'status' => 'available'],
                        ['number' => 'S303', 'floor' => '3', 'status' => 'available'],
                    ],
                ],
                [
                    'type' => [
                        'name' => 'Grand Family Suite',
                        'description' => 'Suite keluarga super luas dengan 2 kamar mandi, dapur mini, dan ruang makan keluarga.',
                        'base_price' => 2100000,
                        'capacity' => 5,
                        'facilities' => ['AC', 'Smart TV 65"', 'High Speed WiFi', 'Double Bathroom & Bathtub', 'Full Kitchenette', 'Dining Area', 'Spacious Living Room', 'Kids Area'],
                        'image_url' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1000&q=80',
                        'is_active' => true,
                    ],
                    'rooms' => [
                        ['number' => 'S401', 'floor' => '4', 'status' => 'available'],
                    ],
                ],
            ],
        ];

        foreach ($branchData as $slug => $itemGroup) {
            $branch = $branches[$slug] ?? null;
            if (!$branch) continue;

            foreach ($itemGroup as $group) {
                $typePayload = array_merge($group['type'], ['hotel_branch_id' => $branch->id]);

                // Create or update room type for specific branch
                $roomType = RoomType::updateOrCreate(
                    [
                        'name' => $group['type']['name'],
                        'hotel_branch_id' => $branch->id,
                    ],
                    $typePayload
                );

                // Create rooms for this room type
                foreach ($group['rooms'] as $roomInfo) {
                    Room::updateOrCreate(
                        [
                            'room_number' => $roomInfo['number'],
                            'hotel_branch_id' => $branch->id,
                        ],
                        [
                            'hotel_branch_id' => $branch->id,
                            'room_type_id' => $roomType->id,
                            'room_number' => $roomInfo['number'],
                            'floor' => $roomInfo['floor'],
                            'status' => $roomInfo['status'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
