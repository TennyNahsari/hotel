<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hall;

class HallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $halls = [
            [
                'name' => 'Ballroom A',
                'hall_type' => 'Ballroom',
                'floor' => 'Ground Floor',
                'capacity' => 300,
                'area_sqm' => 250.00,
                'price_per_hour' => 2000000,
                'facilities' => [
                    'av_equipment' => ['Projector', 'Sound System', '4 Wireless Microphones', 'LED Display'],
                    'furniture' => ['Stage Platform', '50 Round Tables', '300 Chairs', 'Podium'],
                    'tech' => ['High-Speed WiFi', 'Air Conditioning', 'LED Wall'],
                    'other' => ['Parking for 100 cars', 'Catering Pantry', 'Bridal Room']
                ],
                'description' => 'Our grand ballroom perfect for weddings, corporate events, and large gatherings. Features elegant chandeliers and customizable lighting.',
                'status' => 'available',
            ],
            [
                'name' => 'Meeting Room 1',
                'hall_type' => 'Meeting Room Small',
                'floor' => '2nd Floor',
                'capacity' => 20,
                'area_sqm' => 40.00,
                'price_per_hour' => 300000,
                'facilities' => [
                    'av_equipment' => ['Projector', 'Screen', 'Whiteboard', 'Flipchart'],
                    'furniture' => ['Conference Table', '20 Executive Chairs'],
                    'tech' => ['WiFi', 'Video Conference System', 'Air Conditioning'],
                    'other' => ['Coffee/Tea Service', 'Parking']
                ],
                'description' => 'Intimate meeting room ideal for board meetings, interviews, and small team discussions.',
                'status' => 'available',
            ],
            [
                'name' => 'Conference Hall',
                'hall_type' => 'Conference Hall',
                'floor' => '3rd Floor',
                'capacity' => 100,
                'area_sqm' => 120.00,
                'price_per_hour' => 800000,
                'facilities' => [
                    'av_equipment' => ['HD Projector', 'Sound System', '4 Microphones', 'Recording System'],
                    'furniture' => ['Theater Style Seating', 'Tables (Optional)', 'Podium', 'Registration Table'],
                    'tech' => ['High-Speed WiFi', 'Video Conference', 'Air Conditioning'],
                    'other' => ['Parking', 'Break-out Rooms', 'Catering Area']
                ],
                'description' => 'Professional conference hall with modern facilities, perfect for seminars, training, and product launches.',
                'status' => 'available',
            ],
            [
                'name' => 'Function Room',
                'hall_type' => 'Function Room',
                'floor' => '2nd Floor',
                'capacity' => 50,
                'area_sqm' => 75.00,
                'price_per_hour' => 500000,
                'facilities' => [
                    'av_equipment' => ['Projector', 'Sound System', '2 Microphones'],
                    'furniture' => ['Round Tables', '50 Chairs', 'Buffet Tables'],
                    'tech' => ['WiFi', 'Air Conditioning', 'LED Lighting'],
                    'other' => ['Parking', 'Catering Pantry', 'Private Entrance']
                ],
                'description' => 'Versatile function room suitable for birthday parties, anniversary celebrations, and corporate gatherings.',
                'status' => 'available',
            ],
            [
                'name' => 'Meeting Room 2',
                'hall_type' => 'Meeting Room Medium',
                'floor' => '2nd Floor',
                'capacity' => 40,
                'area_sqm' => 60.00,
                'price_per_hour' => 400000,
                'facilities' => [
                    'av_equipment' => ['Smart TV 75"', 'Wireless Presenter', 'Whiteboard'],
                    'furniture' => ['U-Shape Table Setup', '40 Chairs', 'Side Tables'],
                    'tech' => ['WiFi', 'Video Conference', 'Air Conditioning'],
                    'other' => ['Coffee/Tea Station', 'Parking']
                ],
                'description' => 'Modern meeting room with flexible seating arrangements, ideal for workshops and training sessions.',
                'status' => 'available',
            ],
        ];

        foreach ($halls as $hall) {
            Hall::create($hall);
        }

        $this->command->info('✅ Sample halls created successfully!');
    }
}
