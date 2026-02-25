<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Booking;
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

        if ($bookings->isEmpty()) {
            $this->command->warn('⚠️  No bookings found. Please run BookingSeeder first.');
            return;
        }

        if (!$user) {
            $this->command->warn('⚠️  No users found. Please run UserSeeder first.');
            return;
        }

        $payments = [];

        foreach ($bookings as $index => $booking) {
            // Calculate amounts
            $totalAmount = $booking->total_amount ?? 1000000;
            
            if ($index === 0) {
                // Full payment
                $payments[] = [
                    'booking_id' => $booking->id,
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

        foreach ($payments as $payment) {
            Payment::create($payment);
        }

        $this->command->info('✅ Sample payments created successfully! Total: ' . count($payments) . ' payments');
    }
}
