<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Hall;
use App\Models\HallBooking;
use App\Models\HousekeepingTask;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallBookingTest extends TestCase
{
    public function test_hall_booking_non_overlapping_and_cleaning_flow()
    {
        // 1. Create test Hall
        $oldHall = Hall::where('name', 'Test Hall Alpha')->first();
        if ($oldHall) {
            $hbIds = HallBooking::where('hall_id', $oldHall->id)->pluck('id');
            \App\Models\Payment::whereIn('hall_booking_id', $hbIds)->delete();
            \App\Models\HousekeepingTask::where('hall_id', $oldHall->id)->delete();
            HallBooking::where('hall_id', $oldHall->id)->delete();
            $oldHall->delete();
        }
        $hall = Hall::create([
            'name' => 'Test Hall Alpha',
            'hall_type' => 'Ballroom',
            'capacity' => 100,
            'price_per_hour' => 500000,
            'status' => 'available',
        ]);

        $eventDate = now()->addDays(5)->format('Y-m-d');

        // 2. First booking: 08:00 to 10:00
        $booking1 = HallBooking::create([
            'booking_number' => 'HB-TEST-0001',
            'hall_id' => $hall->id,
            'customer_name' => 'Customer One',
            'customer_email' => 'one@example.com',
            'customer_phone' => '08123456789',
            'event_name' => 'Morning Conference',
            'event_date' => $eventDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'duration_hours' => 2,
            'attendees' => 50,
            'total_amount' => 1000000,
            'status' => 'confirmed',
        ]);

        $this->assertNotNull($booking1);

        // 3. Second booking on same date: 12:00 to 14:00 (non-overlapping)
        $isAvailable12to14 = HallBooking::isAvailable($hall->id, $eventDate, '12:00', '14:00');
        $this->assertTrue($isAvailable12to14, 'Slot 12:00-14:00 should be available even if 08:00-10:00 is booked');

        $booking2 = HallBooking::create([
            'booking_number' => 'HB-TEST-0002',
            'hall_id' => $hall->id,
            'customer_name' => 'Customer Two',
            'customer_email' => 'two@example.com',
            'customer_phone' => '08987654321',
            'event_name' => 'Afternoon Workshop',
            'event_date' => $eventDate,
            'start_time' => '12:00',
            'end_time' => '14:00',
            'duration_hours' => 2,
            'attendees' => 30,
            'total_amount' => 1000000,
            'status' => 'confirmed',
        ]);

        $this->assertNotNull($booking2);

        // 4. Overlapping booking test: 09:00 to 11:00 should fail
        $isAvailable09to11 = HallBooking::isAvailable($hall->id, $eventDate, '09:00', '11:00');
        $this->assertFalse($isAvailable09to11, 'Slot 09:00-11:00 must NOT be available as it overlaps with 08:00-10:00');

        // 5. Flow check-in for booking 1
        $hallBookingController = new \App\Http\Controllers\Api\HallBookingController();
        $hallBookingController->checkIn($booking1->id);
        $hall->refresh();
        $this->assertEquals('occupied', $hall->status, 'Hall status should be occupied after checkIn of booking 1');

        // 6. Flow checkout/complete for booking 1 -> hall status becomes dirty, task created
        $hallBookingController->complete($booking1->id);
        $hall->refresh();
        $this->assertEquals('dirty', $hall->status, 'Hall status should be dirty after checkout of booking 1');

        $task = HousekeepingTask::where('hall_id', $hall->id)->latest()->first();
        $this->assertNotNull($task);
        $this->assertEquals('pending', $task->status);

        // 7. Housekeeping starts task -> hall status becomes cleaning
        $housekeepingController = new \App\Http\Controllers\Api\HousekeepingController();
        $housekeepingController->updateStatus(new \Illuminate\Http\Request(['status' => 'in_progress']), $task);
        $hall->refresh();
        $this->assertEquals('cleaning', $hall->status, 'Hall status should be cleaning when housekeeping task is in_progress');

        // 8. Housekeeping completes task -> hall status becomes available
        $housekeepingController->updateStatus(new \Illuminate\Http\Request(['status' => 'completed']), $task);
        $hall->refresh();
        $this->assertEquals('available', $hall->status, 'Hall status should be available after cleaning is completed');

        // 9. Flow check-in for booking 2 -> hall status becomes occupied
        $hallBookingController->checkIn($booking2->id);
        $hall->refresh();
        $this->assertEquals('occupied', $hall->status, 'Hall status should be occupied after checkIn of booking 2');

        // Clean up test data
        $task->delete();
        \App\Models\Payment::whereIn('hall_booking_id', [$booking1->id, $booking2->id])->delete();
        $booking1->delete();
        $booking2->delete();
        $hall->delete();
    }
}
