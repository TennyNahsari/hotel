<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\HallBooking;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $bookings = Booking::with('guest')->take(5)->get();
        $hallBookings = HallBooking::with('hall')->take(3)->get();

        if ($bookings->isEmpty() && $hallBookings->isEmpty()) {
            $this->command->warn('⚠️  No bookings or hall bookings found. Please run BookingSeeder and HallBookingSeeder first.');
            return;
        }

        if (!$user) {
            $this->command->warn('⚠️  No users found. Please run UserSeeder first.');
            return;
        }

        $payments = [];

        // ROOM BOOKING PAYMENTS
        foreach ($bookings as $index => $booking) {
            // Calculate amounts
            $totalAmount = $booking->total_amount ?? 1000000;
            
            if ($index === 0) {
                // Full payment
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'full',
                    'payment_method' => 'transfer',
                    'amount' => $totalAmount,
                    'reference_number' => 'TRF' . rand(1000000, 9999999),
                    'notes' => 'Full payment via bank transfer',
                    'processed_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($index === 1) {
                // Deposit + Partial
                $deposit = $totalAmount * 0.3;
                $partial = $totalAmount * 0.5;
                
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'deposit',
                    'payment_method' => 'cash',
                    'amount' => $deposit,
                    'reference_number' => null,
                    'notes' => '30% deposit payment',
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5),
                ];
                
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'partial',
                    'payment_method' => 'qris',
                    'amount' => $partial,
                    'reference_number' => 'QRIS' . rand(1000000, 9999999),
                    'notes' => 'Partial payment via QRIS',
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ];
            } elseif ($index === 2) {
                // Card payment with extra charge
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'full',
                    'payment_method' => 'card',
                    'amount' => $totalAmount,
                    'reference_number' => 'CARD' . rand(1000000, 9999999),
                    'notes' => 'Credit card payment',
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ];
                
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'extra_charge',
                    'payment_method' => 'cash',
                    'amount' => 150000,
                    'reference_number' => null,
                    'notes' => 'Extra minibar charges',
                    'processed_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                // Cash payment
                $payments[] = [
                    'booking_id' => $booking->id,
                    'hall_booking_id' => null,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'full',
                    'payment_method' => 'cash',
                    'amount' => $totalAmount,
                    'reference_number' => null,
                    'notes' => 'Cash payment at check-in',
                    'processed_by' => $user->id,
                    'created_at' => now()->subHours(3),
                    'updated_at' => now()->subHours(3),
                ];
            }
        }

        // HALL BOOKING PAYMENTS
        foreach ($hallBookings as $index => $hallBooking) {
            $totalAmount = $hallBooking->total_amount ?? 5000000;
            
            if ($index === 0) {
                // Full payment for hall booking
                $payments[] = [
                    'booking_id' => null,
                    'hall_booking_id' => $hallBooking->id,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'full',
                    'payment_method' => 'transfer',
                    'amount' => $totalAmount,
                    'reference_number' => 'TRF' . rand(1000000, 9999999),
                    'notes' => 'Full payment for hall booking - ' . $hallBooking->event_name,
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3),
                ];
            } elseif ($index === 1) {
                // Deposit payment for hall
                $deposit = $totalAmount * 0.5;
                $payments[] = [
                    'booking_id' => null,
                    'hall_booking_id' => $hallBooking->id,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'deposit',
                    'payment_method' => 'transfer',
                    'amount' => $deposit,
                    'reference_number' => 'TRF' . rand(1000000, 9999999),
                    'notes' => '50% deposit for hall booking - ' . $hallBooking->event_name,
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(10),
                ];
            } else {
                // Partial + Extra for hall
                $partial = $totalAmount * 0.7;
                $payments[] = [
                    'booking_id' => null,
                    'hall_booking_id' => $hallBooking->id,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'partial',
                    'payment_method' => 'card',
                    'amount' => $partial,
                    'reference_number' => 'CARD' . rand(1000000, 9999999),
                    'notes' => 'Partial payment for hall booking - ' . $hallBooking->event_name,
                    'processed_by' => $user->id,
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subDays(1),
                ];
                
                $payments[] = [
                    'booking_id' => null,
                    'hall_booking_id' => $hallBooking->id,
                    'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . str_pad(count($payments) + 1, 4, '0', STR_PAD_LEFT),
                    'payment_type' => 'extra_charge',
                    'payment_method' => 'cash',
                    'amount' => 500000,
                    'reference_number' => null,
                    'notes' => 'Additional catering charges',
                    'processed_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($payments as $payment) {
            Payment::create($payment);
        }

        $this->command->info('✅ Sample payments created successfully! Total: ' . count($payments) . ' payments');
        $this->command->info('   - Room booking payments: ' . count(array_filter($payments, fn($p) => $p['booking_id'] !== null)));
        $this->command->info('   - Hall booking payments: ' . count(array_filter($payments, fn($p) => $p['hall_booking_id'] !== null)));
    }
}
