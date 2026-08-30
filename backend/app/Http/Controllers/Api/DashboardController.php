<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\HousekeepingTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Room statistics
        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        // Booking statistics
        $todayCheckIns = Booking::whereDate('check_in_date', $today)
            ->where('status', 'confirmed')
            ->count();
        
        $todayCheckOuts = Booking::whereDate('check_out_date', $today)
            ->where('status', 'checked_in')
            ->count();

        $activeBookings = Booking::where('status', 'checked_in')->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        // Revenue statistics - only from FULL payments
        $todayFullPayments = Payment::whereDate('created_at', $today)
            ->where('payment_type', 'full')
            ->sum('amount');

        $monthFullPayments = Payment::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->where('payment_type', 'full')
            ->sum('amount');

        // All payment types statistics
        $todayAllPayments = Payment::whereDate('created_at', $today)
            ->sum('amount');

        $monthAllPayments = Payment::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->sum('amount');

        // Payment count by type today
        $todayPaymentCount = Payment::whereDate('created_at', $today)
            ->selectRaw('payment_type, count(*) as count, sum(amount) as total')
            ->groupBy('payment_type')
            ->get()
            ->keyBy('payment_type');

        // Housekeeping statistics
        $pendingTasks = HousekeepingTask::where('status', 'pending')->count();
        $inProgressTasks = HousekeepingTask::where('status', 'in_progress')->count();

        // Recent room bookings
        $recentRoomBookings = Booking::with(['guest', 'room.roomType', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($b) {
                $b->booking_type = 'room';
                $b->is_overdue = $b->status === 'pending' && $b->payment_due_at && now()->gt($b->payment_due_at);

                $receiptPayment = $b->payments->first(function ($p) {
                    return !empty($p->receipt_path) || !empty($p->reference_number);
                });

                if ($receiptPayment) {
                    $b->has_receipt = true;
                    $b->reference_number = $receiptPayment->reference_number;
                    if ($receiptPayment->receipt_path) {
                        $cleanPath = str_replace(['public/', 'storage/'], '', $receiptPayment->receipt_path);
                        $b->receipt_url = asset('storage/' . $cleanPath);
                    } else {
                        $b->receipt_url = null;
                    }
                } else {
                    $b->has_receipt = false;
                    $b->receipt_url = null;
                    $b->reference_number = null;
                }

                return $b;
            });

        // Recent hall bookings
        $recentHallBookings = \App\Models\HallBooking::with(['hall', 'guest', 'payments'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($hb) {
                $hb->booking_type = 'hall';
                $hb->is_overdue = $hb->status === 'pending' && $hb->payment_due_at && now()->gt($hb->payment_due_at);

                $receiptPayment = $hb->payments->first(function ($p) {
                    return !empty($p->receipt_path) || !empty($p->reference_number);
                });

                if ($receiptPayment) {
                    $hb->has_receipt = true;
                    $hb->reference_number = $receiptPayment->reference_number;
                    if ($receiptPayment->receipt_path) {
                        $cleanPath = str_replace(['public/', 'storage/'], '', $receiptPayment->receipt_path);
                        $hb->receipt_url = asset('storage/' . $cleanPath);
                    } else {
                        $hb->receipt_url = null;
                    }
                } else {
                    $hb->has_receipt = false;
                    $hb->receipt_url = null;
                    $hb->reference_number = null;
                }

                return $hb;
            });

        return response()->json([
            'rooms' => [
                'total' => $totalRooms,
                'available' => $availableRooms,
                'occupied' => $occupiedRooms,
                'maintenance' => $maintenanceRooms,
            ],
            'bookings' => [
                'today_check_ins' => $todayCheckIns,
                'today_check_outs' => $todayCheckOuts,
                'active' => $activeBookings,
                'pending' => $pendingBookings,
            ],
            'revenue' => [
                'today_full_payments' => $todayFullPayments,
                'month_full_payments' => $monthFullPayments,
                'today_all_payments' => $todayAllPayments,
                'month_all_payments' => $monthAllPayments,
            ],
            'payments' => [
                'today_by_type' => $todayPaymentCount,
            ],
            'housekeeping' => [
                'pending' => $pendingTasks,
                'in_progress' => $inProgressTasks,
            ],
            'recent_bookings' => $recentRoomBookings,
            'recent_hall_bookings' => $recentHallBookings,
        ]);
    }

    /**
     * Auto-cancel overdue bookings that have no uploaded payment receipt.
     */
    public function processOverdueBookings()
    {
        $now = now();

        // 1. Process Room Bookings
        $overdueRoomBookings = Booking::with(['rooms', 'payments'])
            ->where('status', 'pending')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', $now)
            ->get();

        foreach ($overdueRoomBookings as $booking) {
            $hasReceipt = $booking->payments->contains(function ($payment) {
                return !empty($payment->receipt_path) || !empty($payment->reference_number);
            });

            if (!$hasReceipt) {
                $booking->update(['status' => 'cancelled']);

                foreach ($booking->rooms as $room) {
                    if ($room->status === 'booked') {
                        $room->update(['status' => 'available']);
                    }
                }
            }
        }

        // 2. Process Hall Bookings
        $overdueHallBookings = \App\Models\HallBooking::with(['payments'])
            ->where('status', 'pending')
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', $now)
            ->get();

        foreach ($overdueHallBookings as $hallBooking) {
            $hasReceipt = $hallBooking->payments->contains(function ($payment) {
                return !empty($payment->receipt_path) || !empty($payment->reference_number);
            });

            if (!$hasReceipt) {
                $hallBooking->update(['status' => 'cancelled']);
            }
        }
    }

    /**
     * Refresh dashboard & process overdue cancellations.
     */
    public function refresh(Request $request)
    {
        $this->processOverdueBookings();
        return $this->index();
    }
}
