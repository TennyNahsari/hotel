<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HallBooking;
use App\Models\Hall;
use App\Models\Guest;
use App\Models\User;
use Carbon\Carbon;

class HallBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample data
        $halls = Hall::all();
        $guests = Guest::take(3)->get();
        $user = User::first();

        if ($halls->isEmpty()) {
            $this->command->warn('⚠️  No halls found. Please run HallSeeder first.');
            return;
        }

        if (!$user) {
            $this->command->warn('⚠️  No users found. Please run UserSeeder first.');
            return;
        }

        $bookings = [
            // Confirmed booking - Wedding Reception
            [
                'hall_id' => $halls->where('name', 'Ballroom A')->first()?->id ?? $halls->first()->id,
                'guest_id' => $guests->get(0)?->id,
                'customer_name' => 'John Doe & Jane Smith',
                'customer_email' => 'johndoe@example.com',
                'customer_phone' => '081234567890',
                'customer_company' => null,
                'event_name' => 'Wedding Reception',
                'event_date' => Carbon::now()->addDays(30),
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'duration_hours' => 5,
                'attendees' => 250,
                'total_amount' => 10000000,
                'status' => 'confirmed',
                'special_requests' => 'Please arrange flower decorations and a stage for live band performance',
                'notes' => 'VIP client - provide premium service',
                'booked_by' => $user->id,
            ],
            // Pending booking - Corporate Seminar
            [
                'hall_id' => $halls->where('name', 'Conference Hall')->first()?->id ?? $halls->skip(1)->first()?->id ?? $halls->first()->id,
                'guest_id' => null,
                'customer_name' => 'Michael Anderson',
                'customer_email' => 'michael.a@techcorp.com',
                'customer_phone' => '081298765432',
                'customer_company' => 'TechCorp Indonesia',
                'event_name' => 'Digital Marketing Seminar 2026',
                'event_date' => Carbon::now()->addDays(15),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'duration_hours' => 8,
                'attendees' => 80,
                'total_amount' => 6400000,
                'status' => 'pending',
                'special_requests' => 'Need projector, sound system, and lunch catering for 80 people',
                'notes' => null,
                'booked_by' => $user->id,
            ],
            // Confirmed booking - Birthday Party
            [
                'hall_id' => $halls->where('name', 'Function Room')->first()?->id ?? $halls->skip(2)->first()?->id ?? $halls->first()->id,
                'guest_id' => $guests->get(1)?->id,
                'customer_name' => 'Sarah Williams',
                'customer_email' => 'sarah.w@email.com',
                'customer_phone' => '081234509876',
                'customer_company' => null,
                'event_name' => '50th Birthday Celebration',
                'event_date' => Carbon::now()->addDays(7),
                'start_time' => '19:00:00',
                'end_time' => '22:00:00',
                'duration_hours' => 3,
                'attendees' => 40,
                'total_amount' => 1500000,
                'status' => 'confirmed',
                'special_requests' => 'Birthday cake table, balloon decorations',
                'notes' => 'Surprise party - keep decorations hidden until 7 PM',
                'booked_by' => $user->id,
            ],
            // Completed booking - Past event
            [
                'hall_id' => $halls->where('name', 'Meeting Room 2')->first()?->id ?? $halls->skip(3)->first()?->id ?? $halls->first()->id,
                'guest_id' => null,
                'customer_name' => 'David Chen',
                'customer_email' => 'david.chen@startup.io',
                'customer_phone' => '081287654321',
                'customer_company' => 'Startup Innovations',
                'event_name' => 'Product Launch Workshop',
                'event_date' => Carbon::now()->subDays(5),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'duration_hours' => 4,
                'attendees' => 35,
                'total_amount' => 1600000,
                'status' => 'completed',
                'special_requests' => 'U-shape seating arrangement, whiteboard markers',
                'notes' => 'Event completed successfully. Customer satisfied.',
                'booked_by' => $user->id,
            ],
            // Pending booking - Team Meeting
            [
                'hall_id' => $halls->where('name', 'Meeting Room 1')->first()?->id ?? $halls->skip(4)->first()?->id ?? $halls->first()->id,
                'guest_id' => $guests->get(2)?->id,
                'customer_name' => 'Linda Martinez',
                'customer_email' => 'linda.m@company.com',
                'customer_phone' => '081234567123',
                'customer_company' => 'Global Solutions Ltd',
                'event_name' => 'Quarterly Team Meeting',
                'event_date' => Carbon::now()->addDays(3),
                'start_time' => '13:00:00',
                'end_time' => '16:00:00',
                'duration_hours' => 3,
                'attendees' => 15,
                'total_amount' => 900000,
                'status' => 'pending',
                'special_requests' => 'Coffee and tea service',
                'notes' => null,
                'booked_by' => $user->id,
            ],
            // Cancelled booking
            [
                'hall_id' => $halls->where('name', 'Conference Hall')->first()?->id ?? $halls->skip(1)->first()?->id ?? $halls->first()->id,
                'guest_id' => null,
                'customer_name' => 'Robert Johnson',
                'customer_email' => 'robert.j@events.com',
                'customer_phone' => '081298761234',
                'customer_company' => 'Event Masters',
                'event_name' => 'Annual Gala Dinner',
                'event_date' => Carbon::now()->addDays(20),
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
                'duration_hours' => 4,
                'attendees' => 100,
                'total_amount' => 3200000,
                'status' => 'cancelled',
                'special_requests' => 'Theater style seating, stage setup',
                'notes' => 'Cancelled by customer - venue changed',
                'booked_by' => $user->id,
            ],
        ];

        foreach ($bookings as $bookingData) {
            // Generate booking number
            $date = Carbon::parse($bookingData['event_date'])->format('Ymd');
            $count = HallBooking::whereDate('event_date', $bookingData['event_date'])->count() + 1;
            $bookingData['booking_number'] = 'HB-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            HallBooking::create($bookingData);
        }

        $this->command->info('✅ Sample hall bookings created successfully!');
    }
}
