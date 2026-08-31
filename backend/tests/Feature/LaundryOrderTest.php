<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\LaundryOrder;
use App\Models\User;
use Illuminate\Support\Str;

class LaundryOrderTest extends TestCase
{
    public function test_laundry_order_creation_for_room_and_hall_bookings()
    {
        $user = User::factory()->create();
        $guest = Guest::create(['name' => 'Laundry Guest', 'email' => 'lguest@example.com', 'phone' => '081111222333']);

        $bkNumber = 'BK-LD-' . Str::random(6);
        $hbNumber = 'HB-LD-' . Str::random(6);

        // 1. Create Room Booking
        $roomBooking = Booking::create([
            'booking_number' => $bkNumber,
            'guest_id' => $guest->id,
            'check_in_date' => now()->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'nights' => 2,
            'total_amount' => 500000,
            'status' => 'checked_in',
        ]);

        // Create Laundry Order for Room Booking
        $responseRoom = $this->actingAs($user)->postJson('/api/laundry-orders', [
            'booking_id' => $roomBooking->id,
            'weight_kg' => 2.5,
            'price_per_kg' => 10000,
            'notes' => 'Room laundry service',
        ]);
        $responseRoom->assertStatus(201);
        $this->assertDatabaseHas('laundry_orders', [
            'booking_id' => $roomBooking->id,
            'weight_kg' => 2.5,
            'total_amount' => 25000,
        ]);

        // 2. Create Hall & Hall Booking
        $hall = Hall::create([
            'name' => 'Laundry Hall ' . Str::random(5),
            'hall_type' => 'Function Room',
            'capacity' => 50,
            'price_per_hour' => 200000,
            'status' => 'available',
        ]);

        $hallBooking = HallBooking::create([
            'booking_number' => $hbNumber,
            'hall_id' => $hall->id,
            'customer_name' => 'Hall Customer',
            'customer_email' => 'hcustomer@example.com',
            'customer_phone' => '082222333444',
            'event_name' => 'Hall Event',
            'event_date' => now()->addDays(1)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'duration_hours' => 2,
            'attendees' => 20,
            'total_amount' => 400000,
            'status' => 'confirmed',
        ]);

        // Create Laundry Order for Hall Booking
        $responseHall = $this->actingAs($user)->postJson('/api/laundry-orders', [
            'hall_booking_id' => $hallBooking->id,
            'weight_kg' => 5.0,
            'price_per_kg' => 10000,
            'notes' => 'Hall tablecloth laundry',
        ]);
        $responseHall->assertStatus(201);
        $this->assertDatabaseHas('laundry_orders', [
            'hall_booking_id' => $hallBooking->id,
            'weight_kg' => 5.0,
            'total_amount' => 50000,
        ]);

        // Cleanup
        \App\Models\Payment::where('booking_id', $roomBooking->id)->delete();
        \App\Models\Payment::where('hall_booking_id', $hallBooking->id)->delete();
        LaundryOrder::where('booking_id', $roomBooking->id)->delete();
        LaundryOrder::where('hall_booking_id', $hallBooking->id)->delete();
        $roomBooking->delete();
        $hallBooking->delete();
        $hall->delete();
        $guest->delete();
        $user->delete();
    }
}
