<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hall;
use App\Models\HotelBranch;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            'jakarta' => HotelBranch::where('slug', 'jakarta')->first() ?: HotelBranch::firstOrCreate(['slug' => 'jakarta'], [
                'name' => 'Grand Hotel Merdeka Jakarta',
                'address' => 'Jl. Jendral Sudirman No. 45, Jakarta Pusat',
                'city' => 'Jakarta',
                'phone' => '021-5550123',
                'email' => 'jakarta@grandhotelmerdeka.com',
                'description' => 'Cabang utama kami di pusat bisnis kota Jakarta dengan fasilitas bintang 5.',
                'is_active' => true,
            ]),
            'bali' => HotelBranch::where('slug', 'bali')->first() ?: HotelBranch::firstOrCreate(['slug' => 'bali'], [
                'name' => 'Grand Hotel Merdeka Bali',
                'address' => 'Jl. Pantai Kuta No. 88, Badung, Bali',
                'city' => 'Bali',
                'phone' => '0361-7770456',
                'email' => 'bali@grandhotelmerdeka.com',
                'description' => 'Resort kemewahan di pinggir pantai Kuta Bali dengan pemandangan sunset memukau.',
                'is_active' => true,
            ]),
            'surabaya' => HotelBranch::where('slug', 'surabaya')->first() ?: HotelBranch::firstOrCreate(['slug' => 'surabaya'], [
                'name' => 'Grand Hotel Merdeka Surabaya',
                'address' => 'Jl. Tunjungan No. 12, Surabaya',
                'city' => 'Surabaya',
                'phone' => '031-4440789',
                'email' => 'surabaya@grandhotelmerdeka.com',
                'description' => 'Hotel bisnis strategis di jantung kota Surabaya dengan fasilitas konvensi modern.',
                'is_active' => true,
            ]),
        ];

        $allHalls = [
            // --- JAKARTA BRANCH ---
            [
                'branch_key' => 'jakarta',
                'name' => 'Grand Ballroom Merdeka',
                'hall_type' => 'Ballroom',
                'floor' => 'Ground Floor',
                'capacity' => 300,
                'area_sqm' => 250.00,
                'price_per_hour' => 2500000,
                'facilities' => [
                    'av_equipment' => ['Projector 4K', 'Sound System 10.000W', '6 Wireless Microphones', 'LED Wall 6x3m'],
                    'furniture' => ['Stage Platform 8x4m', '50 Round Tables', '300 Banquet Chairs', 'Podium'],
                    'tech' => ['High-Speed WiFi', 'Centralized AC', 'Customizable RGB Lighting'],
                    'other' => ['VIP Lounge', 'Catering Pantry', 'Bridal Room', 'Dedicated Parking']
                ],
                'description' => 'Grand ballroom termewah di Jakarta untuk pesta pernikahan megah, galas, dan konvensi internasional.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'jakarta',
                'name' => 'Sudirman Boardroom',
                'hall_type' => 'Meeting Room Small',
                'floor' => '2nd Floor',
                'capacity' => 15,
                'area_sqm' => 35.00,
                'price_per_hour' => 350000,
                'facilities' => [
                    'av_equipment' => ['Smart TV 75"', 'Video Conference Bar', 'Wireless Screen Sharing'],
                    'furniture' => ['Executive Mahogany Table', '15 Leather Chairs', 'Whiteboard'],
                    'tech' => ['High-Speed Fiber WiFi', 'Quiet Inverter AC'],
                    'other' => ['Executive Coffee & Tea Service', 'Free Mineral Water']
                ],
                'description' => 'Ruang rapat eksklusif dengan privasi tinggi untuk rapat direksi dan negosiasi bisnis penting.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'jakarta',
                'name' => 'Nusantara Convention Hall',
                'hall_type' => 'Conference Hall',
                'floor' => '3rd Floor',
                'capacity' => 150,
                'area_sqm' => 160.00,
                'price_per_hour' => 1200000,
                'facilities' => [
                    'av_equipment' => ['Dual HD Projectors', 'Line Array Sound System', '4 Clip-on Microphones'],
                    'furniture' => ['Theater Style Seating', 'Stage Podium', 'Registration Counter'],
                    'tech' => ['Dedicated WiFi Access Point', 'Live Streaming Setup Ready'],
                    'other' => ['Breakout Room Access', 'Foyer Area for Coffee Break']
                ],
                'description' => 'Aula konvensi modern untuk seminar nasional, workshop, dan peluncuran produk.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'jakarta',
                'name' => 'Batavia Function Room',
                'hall_type' => 'Function Room',
                'floor' => '2nd Floor',
                'capacity' => 60,
                'area_sqm' => 80.00,
                'price_per_hour' => 600000,
                'facilities' => [
                    'av_equipment' => ['Laser Projector', 'Sound System', '2 Wireless Microphones'],
                    'furniture' => ['Flexible Round/Square Tables', '60 Padded Chairs', 'Buffet Table Setup'],
                    'tech' => ['WiFi', 'Individual Climate Control'],
                    'other' => ['Private Balcony', 'Restroom Access']
                ],
                'description' => 'Ruang serbaguna bernuansa hangat untuk acara ulang tahun, gathering perusahaan, dan syukuran.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'jakarta',
                'name' => 'Thamrin Meeting Lounge',
                'hall_type' => 'Meeting Room Medium',
                'floor' => '2nd Floor',
                'capacity' => 35,
                'area_sqm' => 55.00,
                'price_per_hour' => 450000,
                'facilities' => [
                    'av_equipment' => ['Interactive Touch Screen 85"', 'Soundbar', 'Flipchart'],
                    'furniture' => ['U-Shape / Classroom Setup', '35 Ergonomic Chairs'],
                    'tech' => ['High-Speed WiFi', 'AC'],
                    'other' => ['Coffee Break Station']
                ],
                'description' => 'Ruang rapat menengah yang nyaman untuk pelatihan internal dan workshop interaktif.',
                'status' => 'available',
            ],

            // --- BALI BRANCH ---
            [
                'branch_key' => 'bali',
                'name' => 'Sunset Ocean Ballroom Bali',
                'hall_type' => 'Ballroom',
                'floor' => 'Ground Floor',
                'capacity' => 250,
                'area_sqm' => 280.00,
                'price_per_hour' => 3000000,
                'facilities' => [
                    'av_equipment' => ['Outdoor & Indoor Sound System', 'Full HD Projection System', '6 Microphones'],
                    'furniture' => ['Teakwood Stage', '40 Round Banquet Tables', '250 Tiffany Chairs'],
                    'tech' => ['High-Speed Resort WiFi', 'Ambient Outdoor Lighting'],
                    'other' => ['Direct Beach Access', 'Oceanfront Terrace', 'Private Bar Counter']
                ],
                'description' => 'Ballroom mewah tepi pantai dengan akses pemandangan laut Kuta Bali yang indah untuk beachfront wedding.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'bali',
                'name' => 'Kuta Pavilion Meeting Room',
                'hall_type' => 'Meeting Room Small',
                'floor' => '1st Floor',
                'capacity' => 20,
                'area_sqm' => 45.00,
                'price_per_hour' => 400000,
                'facilities' => [
                    'av_equipment' => ['Smart TV 65"', 'High Quality Speaker', 'Whiteboard'],
                    'furniture' => ['Rattan & Wood Conference Table', '20 Comfort Chairs'],
                    'tech' => ['WiFi', 'Air Conditioning'],
                    'other' => ['Tropical Garden View', 'Fresh Coconut Water Welcome Drink']
                ],
                'description' => 'Ruang rapat berkonsep paviliun tropis untuk rapat santai namun produktif di Bali.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'bali',
                'name' => 'Dewata International Convention Hall',
                'hall_type' => 'Conference Hall',
                'floor' => '2nd Floor',
                'capacity' => 200,
                'area_sqm' => 220.00,
                'price_per_hour' => 1500000,
                'facilities' => [
                    'av_equipment' => ['Dual Projectors', 'Surround Sound System', 'Simultaneous Interpretation Booth Ready'],
                    'furniture' => ['Auditorium Seating', 'Podium', 'Press Registration Desk'],
                    'tech' => ['High-Density WiFi', 'AC'],
                    'other' => ['VIP Waiting Lounge', 'Spacious Exhibition Foyer']
                ],
                'description' => 'Aula konvensi berstandar internasional untuk konferensi, summit, dan pameran pariwisata.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'bali',
                'name' => 'Sanur Oceanview Function Room',
                'hall_type' => 'Function Room',
                'floor' => '2nd Floor',
                'capacity' => 80,
                'area_sqm' => 100.00,
                'price_per_hour' => 750000,
                'facilities' => [
                    'av_equipment' => ['Projector System', 'Sound System', 'Microphones'],
                    'furniture' => ['Cocktail Tables', '80 Seated Dining Chairs', 'DJ Booth Space'],
                    'tech' => ['High-Speed WiFi', 'Dimmable Lighting'],
                    'other' => ['Open Air Deck', 'Buffet Corner']
                ],
                'description' => 'Ruang fungsi terbuka dengan dek pemandangan laut Bali untuk cocktail party & dinner.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'bali',
                'name' => 'Ubud Creative Workshop Room',
                'hall_type' => 'Meeting Room Medium',
                'floor' => '1st Floor',
                'capacity' => 40,
                'area_sqm' => 65.00,
                'price_per_hour' => 500000,
                'facilities' => [
                    'av_equipment' => ['Short-throw Projector', 'Bluetooth Sound System', 'Mobile Whiteboards'],
                    'furniture' => ['Modular Tables', '40 Wooden Chairs'],
                    'tech' => ['WiFi', 'Air Conditioning'],
                    'other' => ['Garden Terrace for Coffee Break']
                ],
                'description' => 'Ruang kreatif dengan pencahayaan alami alami untuk retreat, workshop seni, dan team building.',
                'status' => 'available',
            ],

            // --- SURABAYA BRANCH ---
            [
                'branch_key' => 'surabaya',
                'name' => 'Pahlawan Grand Ballroom Surabaya',
                'hall_type' => 'Ballroom',
                'floor' => 'Ground Floor',
                'capacity' => 350,
                'area_sqm' => 300.00,
                'price_per_hour' => 2200000,
                'facilities' => [
                    'av_equipment' => ['Dual High-Lumen Projectors', 'Professional Audio System', 'Wireless Mics'],
                    'furniture' => ['Large Stage', '55 Round Tables', '350 Banquet Chairs', 'Red Carpet Entrance'],
                    'tech' => ['High-Speed WiFi', 'Central AC'],
                    'other' => ['Loading Dock Access', 'Holding Room', 'Kitchen Pantry']
                ],
                'description' => 'Ballroom terbesar di Surabaya untuk pameran industri, resepsi megah, dan kejuaraan.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'surabaya',
                'name' => 'Tunjungan Executive Boardroom',
                'hall_type' => 'Meeting Room Small',
                'floor' => '2nd Floor',
                'capacity' => 18,
                'area_sqm' => 40.00,
                'price_per_hour' => 300000,
                'facilities' => [
                    'av_equipment' => ['4K Commercial TV 75"', 'Video Conference System', 'Glass Whiteboard'],
                    'furniture' => ['Conference Board Table', '18 Leather Chairs'],
                    'tech' => ['High-Speed WiFi', 'AC'],
                    'other' => ['Coffee Machine', 'Snack Bar']
                ],
                'description' => 'Ruang rapat direksi premium di jantung pusat bisnis Surabaya.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'surabaya',
                'name' => 'Majapahit Auditorium Hall',
                'hall_type' => 'Conference Hall',
                'floor' => '3rd Floor',
                'capacity' => 120,
                'area_sqm' => 140.00,
                'price_per_hour' => 900000,
                'facilities' => [
                    'av_equipment' => ['Projector', 'Full Sound System', 'Podium Mic & 3 Handheld Mics'],
                    'furniture' => ['Tiered Auditorium Seating', 'Speaker Desk'],
                    'tech' => ['WiFi', 'AC'],
                    'other' => ['Foyer for Registration']
                ],
                'description' => 'Auditorium bergaya klasik-modern untuk kuliah umum, seminar bisnis, dan pertemuaan asosiasi.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'surabaya',
                'name' => 'Suroboyo Event Function Room',
                'hall_type' => 'Function Room',
                'floor' => '2nd Floor',
                'capacity' => 70,
                'area_sqm' => 85.00,
                'price_per_hour' => 550000,
                'facilities' => [
                    'av_equipment' => ['Projector', 'PA System', 'Microphone'],
                    'furniture' => ['Round Tables & Chairs', 'Buffet Service Line'],
                    'tech' => ['WiFi', 'AC'],
                    'other' => ['Dedicated Service Staff']
                ],
                'description' => 'Ruang acara serbaguna untuk gathering keluarga, reuni, dan seminar komunitas.',
                'status' => 'available',
            ],
            [
                'branch_key' => 'surabaya',
                'name' => 'Bung Tomo Meeting Room',
                'hall_type' => 'Meeting Room Medium',
                'floor' => '2nd Floor',
                'capacity' => 45,
                'area_sqm' => 60.00,
                'price_per_hour' => 450000,
                'facilities' => [
                    'av_equipment' => ['Smart Projector', 'Wireless Sound', 'Whiteboard'],
                    'furniture' => ['Classroom / Boardroom Setup', '45 Chairs'],
                    'tech' => ['WiFi', 'AC'],
                    'other' => ['Coffee Break Service']
                ],
                'description' => 'Ruang rapat fleksibel dengan fasilitas lengkap untuk pelatihan karyawan dan presentasi tim.',
                'status' => 'available',
            ],
        ];

        // Legacy cleanup: Update old generic halls to Jakarta if no branch set
        $jakartaBranch = $branches['jakarta'];
        Hall::whereNull('hotel_branch_id')->update(['hotel_branch_id' => $jakartaBranch->id]);

        foreach ($allHalls as $hallData) {
            $branchKey = $hallData['branch_key'];
            $branch = $branches[$branchKey] ?? $jakartaBranch;
            unset($hallData['branch_key']);

            $hallData['hotel_branch_id'] = $branch->id;
            if (isset($hallData['facilities']) && is_array($hallData['facilities'])) {
                $hallData['facilities'] = json_encode($hallData['facilities']);
            }

            Hall::updateOrCreate(
                [
                    'name' => $hallData['name'],
                    'hotel_branch_id' => $branch->id,
                ],
                $hallData
            );
        }

        $this->command->info('✅ Sample halls created successfully for Jakarta, Bali, and Surabaya branches!');
    }
}
