<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MLDummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds to generate dummy data for ML training
     * Generates 6 months of data for bookings, hall bookings, and restaurant orders
     */
    public function run(): void
    {
        $this->command->info('🚀 Generating ML dummy data...');

        // Clear existing ML dummy data
        $this->command->info('Clearing existing ML data...');
        
        // Delete in order (respecting foreign keys)
        DB::table('restaurant_order_items')->delete();
        DB::table('restaurant_orders')->delete();
        DB::table('payments')->delete(); // Delete payments first (references bookings & hall_bookings)
        DB::table('booking_rooms')->delete();
        DB::table('hall_bookings')->delete();
        DB::table('bookings')->delete();
        
        $this->command->info('✓ Cleared old data');

        // Ensure we have guests and rooms
        $guests = DB::table('guests')->pluck('id');
        $rooms = DB::table('rooms')->pluck('id');
        $roomTypes = DB::table('room_types')->pluck('id');
        $halls = DB::table('halls')->pluck('id');
        $menuItems = DB::table('menu_items')->pluck('id');

        if ($guests->isEmpty() || $rooms->isEmpty()) {
            $this->command->error('Please seed guests and rooms first!');
            return;
        }

        // Generate bookings (6 months, ~100 records)
        $this->generateBookings($guests, $rooms, $roomTypes);

        // Generate hall bookings
        $this->generateHallBookings($guests, $halls);

        // Generate restaurant orders
        $this->generateRestaurantOrders($menuItems);

        $this->command->info('✅ ML dummy data generated successfully!');
    }

    /**
     * Generate booking data with realistic patterns
     * - Higher demand on weekends
     * - Seasonal peaks (holidays)
     * - Random variations
     */
    private function generateBookings($guests, $rooms, $roomTypes)
    {
        $this->command->info('Generating bookings...');
        
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();
        $bookings = [];
        $bookingRooms = [];

        $bookingCount = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $isWeekend = $date->isWeekend();
            $isHoliday = $this->isHoliday($date);

            // Booking probability
            // Weekday: 30%, Weekend: 60%, Holiday: 80%
            $probability = $isHoliday ? 0.8 : ($isWeekend ? 0.6 : 0.3);
            
            // Random bookings for this day (0-3 bookings)
            $dailyBookings = 0;
            for ($i = 0; $i < 3; $i++) {
                if (mt_rand() / mt_getrandmax() < $probability) {
                    $dailyBookings++;
                }
            }

            for ($i = 0; $i < $dailyBookings; $i++) {
                $checkInDate = $date->copy();
                $nights = $isWeekend ? rand(2, 4) : rand(1, 3);
                $checkOutDate = $checkInDate->copy()->addDays($nights);

                // Select random guest and room
                $guestId = $guests->random();
                $roomId = $rooms->random();

                $basePrice = rand(300000, 800000);
                $totalAmount = $basePrice * $nights;

                // Booking status distribution
                $statuses = ['confirmed', 'checked_in', 'checked_out', 'checked_out', 'checked_out'];
                $status = $statuses[array_rand($statuses)];

                // Generate booking number
                $bookingNumber = 'BKG-' . $checkInDate->format('Ymd') . '-' . str_pad($bookingCount + 1, 4, '0', STR_PAD_LEFT);

                $bookingId = DB::table('bookings')->insertGetId([
                    'booking_number' => $bookingNumber,
                    'guest_id' => $guestId,
                    'check_in_date' => $checkInDate->toDateString(),
                    'check_out_date' => $checkOutDate->toDateString(),
                    'nights' => $nights,
                    'adults' => rand(1, 2),
                    'children' => rand(0, 2),
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'created_at' => $checkInDate->copy()->subDays(rand(1, 30)),
                    'updated_at' => now()
                ]);

                // Create booking_room entry
                DB::table('booking_rooms')->insert([
                    'booking_id' => $bookingId,
                    'room_id' => $roomId,
                    'room_rate' => $basePrice,
                    'nights' => $nights,
                    'subtotal' => $totalAmount,
                    'check_in_date' => $checkInDate->toDateString(),
                    'check_out_date' => $checkOutDate->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $bookingCount++;
            }
        }

        $this->command->info("✓ Generated {$bookingCount} bookings");
    }

    /**
     * Generate hall booking data
     * - Peak on weekends
     * - Higher demand on holidays
     */
    private function generateHallBookings($guests, $halls)
    {
        if ($halls->isEmpty()) {
            $this->command->warn('No halls found, skipping hall bookings');
            return;
        }

        $this->command->info('Generating hall bookings...');
        
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now()->addMonths(1); // Future bookings
        $count = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $isWeekend = $date->isWeekend();
            $isHoliday = $this->isHoliday($date);

            // Hall booking probability
            $probability = $isHoliday ? 0.7 : ($isWeekend ? 0.5 : 0.1);

            if (mt_rand() / mt_getrandmax() < $probability) {
                $guestId = $guests->random();
                $hallId = $halls->random();

                $durationHours = $isWeekend ? rand(4, 8) : rand(2, 6);
                $pricePerHour = rand(200000, 500000);
                $totalAmount = $pricePerHour * $durationHours;

                // Status distribution
                if ($date->isPast()) {
                    $statuses = ['confirmed', 'completed', 'completed', 'completed'];
                } else {
                    $statuses = ['confirmed', 'confirmed', 'pending'];
                }
                $status = $statuses[array_rand($statuses)];

                // Get guest info
                $guest = DB::table('guests')->find($guestId);
                
                // Get first user as booked_by
                $firstUserId = DB::table('users')->first()->id ?? 1;

                DB::table('hall_bookings')->insert([
                    'booking_number' => 'HB-' . $date->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT),
                    'guest_id' => $guestId,
                    'hall_id' => $hallId,
                    'customer_name' => $guest->name ?? 'Guest ' . $guestId,
                    'customer_email' => $guest->email ?? "guest{$guestId}@example.com",
                    'customer_phone' => $guest->phone ?? '081234567890',
                    'customer_company' => rand(0, 1) ? 'Company ' . rand(1, 50) : null,
                    'event_name' => $this->getRandomEventName(),
                    'event_date' => $date->toDateString(),
                    'start_time' => '10:00:00',
                    'end_time' => sprintf('%02d:00:00', 10 + $durationHours),
                    'duration_hours' => $durationHours,
                    'attendees' => rand(30, 200),
                    'total_amount' => $totalAmount,
                    'status' => $status,
                    'booked_by' => $firstUserId,
                    'created_at' => $date->copy()->subDays(rand(7, 45)),
                    'updated_at' => now()
                ]);

                $count++;
            }
        }

        $this->command->info("✓ Generated {$count} hall bookings");
    }

    /**
     * Generate restaurant order data
     */
    private function generateRestaurantOrders($menuItems)
    {
        if ($menuItems->isEmpty()) {
            $this->command->warn('No menu items found, skipping restaurant orders');
            return;
        }

        $this->command->info('Generating restaurant orders...');
        
        // Get bookings to link orders to
        $bookings = DB::table('bookings')
            ->where('status', '!=', 'cancelled')
            ->pluck('id');

        if ($bookings->isEmpty()) {
            $this->command->warn('No bookings found, skipping restaurant orders');
            return;
        }

        $orderCount = 0;
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        // Get first user as created_by
        $firstUserId = DB::table('users')->first()->id ?? 1;

        // Generate daily orders
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $isWeekend = $date->isWeekend();
            
            // Daily orders: 5-20 on weekdays, 10-30 on weekends
            $dailyOrders = $isWeekend ? rand(10, 30) : rand(5, 20);

            for ($i = 0; $i < $dailyOrders; $i++) {
                $bookingId = $bookings->random();

                // Order time (breakfast, lunch, dinner)
                $mealTimes = ['08:00:00', '12:00:00', '19:00:00'];
                $orderTime = $mealTimes[array_rand($mealTimes)];

                $orderDate = $date->copy()->setTimeFromTimeString($orderTime);

                $orderId = DB::table('restaurant_orders')->insertGetId([
                    'order_number' => 'RO-' . $orderDate->format('Ymd') . '-' . str_pad($orderCount + 1, 4, '0', STR_PAD_LEFT),
                    'booking_id' => $bookingId,
                    'total_amount' => 0, // Will calculate later
                    'status' => 'delivered',
                    'created_by' => $firstUserId,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate
                ]);

                // Add order items (1-5 items per order)
                $itemCount = rand(1, 5);
                $totalAmount = 0;

                for ($j = 0; $j < $itemCount; $j++) {
                    $menuItemId = $menuItems->random();
                    
                    // Get menu item price (or use random if not found)
                    $menuItem = DB::table('menu_items')->find($menuItemId);
                    $price = $menuItem ? $menuItem->price : rand(25000, 150000);
                    
                    $quantity = rand(1, 3);
                    $subtotal = $price * $quantity;
                    $totalAmount += $subtotal;

                    DB::table('restaurant_order_items')->insert([
                        'restaurant_order_id' => $orderId,
                        'menu_item_id' => $menuItemId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate
                    ]);
                }

                // Update order total
                DB::table('restaurant_orders')
                    ->where('id', $orderId)
                    ->update(['total_amount' => $totalAmount]);

                $orderCount++;
            }
        }

        $this->command->info("✓ Generated {$orderCount} restaurant orders");
    }

    /**
     * Check if date is a holiday (simplified Indonesian holidays)
     */
    private function isHoliday(Carbon $date): bool
    {
        $holidays = [
            '01-01', // New Year
            '05-01', // Labor Day
            '08-17', // Independence Day
            '12-25', // Christmas
        ];

        return in_array($date->format('m-d'), $holidays);
    }

    /**
     * Get random event name
     */
    private function getRandomEventName(): string
    {
        $events = [
            'Wedding Reception',
            'Birthday Party',
            'Corporate Meeting',
            'Conference',
            'Seminar',
            'Workshop',
            'Family Gathering',
            'Graduation Party',
            'Anniversary Celebration'
        ];

        return $events[array_rand($events)];
    }
}
