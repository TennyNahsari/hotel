<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HallBooking;
use App\Models\Hall;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\LaundryOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HallBookingController extends Controller
{
    /**
     * Display a listing of hall bookings.
     */
    public function index(Request $request)
    {
        // Auto process overdue cancellations
        app(\App\Http\Controllers\Api\DashboardController::class)->processOverdueBookings();

        $query = HallBooking::with(['hall', 'guest', 'bookedBy', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by hall
        if ($request->filled('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('event_date', [$request->start_date, $request->end_date]);
        }

        // Filter by event_date
        if ($request->filled('event_date')) {
            $query->whereDate('event_date', $request->event_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('event_name', 'like', '%' . $search . '%');
            });
        }

        // Sort (default: latest created first)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $bookings = $query->paginate($request->get('per_page', 15));

        return response()->json($bookings);
    }

    /**
     * Store a newly created hall booking.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hall_id' => 'required|exists:halls,id',
            'guest_id' => 'nullable|exists:guests,id',
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_company' => 'nullable|string|max:100',
            'event_name' => 'required|string|max:200',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'attendees' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get hall
        $hall = Hall::findOrFail($request->hall_id);

        // Validate capacity
        if ($request->attendees > $hall->capacity) {
            return response()->json([
                'errors' => ['attendees' => ['Number of attendees exceeds hall capacity of ' . $hall->capacity]]
            ], 422);
        }

        // Check availability
        $isAvailable = HallBooking::isAvailable(
            $request->hall_id,
            $request->event_date,
            $request->start_time,
            $request->end_time
        );

        if (!$isAvailable) {
            return response()->json([
                'errors' => ['event_date' => ['Hall is not available for the selected date and time']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Calculate duration and total
            $duration = HallBooking::calculateDuration($request->start_time, $request->end_time);
            $total = HallBooking::calculateTotal($request->hall_id, $duration);

            // Create booking
            $booking = HallBooking::create([
                'booking_number' => HallBooking::generateBookingNumber(),
                'hall_id' => $request->hall_id,
                'guest_id' => $request->guest_id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company,
                'event_name' => $request->event_name,
                'event_date' => $request->event_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_hours' => $duration,
                'attendees' => $request->attendees,
                'total_amount' => $total,
                'deposit_amount' => $request->deposit_amount ?? 0,
                'status' => 'pending',
                'special_requests' => $request->special_requests,
                'notes' => $request->notes,
                'booked_by' => auth()->id(),
            ]);

            $this->updateHallStatus($booking->hall_id);

            DB::commit();

            return response()->json([
                'message' => 'Hall booking created successfully',
                'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified hall booking.
     */
    public function show($id)
    {
        $booking = HallBooking::with(['hall', 'guest', 'bookedBy', 'payments.processedBy'])
            ->findOrFail($id);

        return response()->json($booking);
    }

    /**
     * Update the specified hall booking.
     */
    public function update(Request $request, $id)
    {
        $booking = HallBooking::findOrFail($id);

        // Cannot update completed or cancelled bookings
        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot update ' . $booking->status . ' booking'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'hall_id' => 'required|exists:halls,id',
            'guest_id' => 'nullable|exists:guests,id',
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_company' => 'nullable|string|max:100',
            'event_name' => 'required|string|max:200',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'attendees' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get hall
        $hall = Hall::findOrFail($request->hall_id);

        // Validate capacity
        if ($request->attendees > $hall->capacity) {
            return response()->json([
                'errors' => ['attendees' => ['Number of attendees exceeds hall capacity of ' . $hall->capacity]]
            ], 422);
        }

        // Check availability (exclude current booking)
        $isAvailable = HallBooking::isAvailable(
            $request->hall_id,
            $request->event_date,
            $request->start_time,
            $request->end_time,
            $id
        );

        if (!$isAvailable) {
            return response()->json([
                'errors' => ['event_date' => ['Hall is not available for the selected date and time']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Calculate duration and total
            $duration = HallBooking::calculateDuration($request->start_time, $request->end_time);
            $total = HallBooking::calculateTotal($request->hall_id, $duration);

            $booking->update([
                'hall_id' => $request->hall_id,
                'guest_id' => $request->guest_id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company,
                'event_name' => $request->event_name,
                'event_date' => $request->event_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'duration_hours' => $duration,
                'attendees' => $request->attendees,
                'total_amount' => $total,
                'special_requests' => $request->special_requests,
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Hall booking updated successfully',
                'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified hall booking.
     */
    public function destroy($id)
    {
        $booking = HallBooking::findOrFail($id);

        // Only pending or cancelled bookings can be deleted
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return response()->json([
                'message' => 'Only pending or cancelled bookings can be deleted'
            ], 400);
        }

        $hallId = $booking->hall_id;
        $this->deleteBookingReceipts($booking);
        $booking->payments()->delete();
        $booking->delete();
        $this->updateHallStatus($hallId);

        return response()->json([
            'message' => 'Hall booking deleted successfully'
        ]);
    }

    /**
     * Confirm a hall booking.
     */
    public function confirm($id)
    {
        $booking = HallBooking::findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Only pending or confirmed bookings can be confirmed'
            ], 400);
        }

        $booking->update(['status' => 'confirmed']);
        $this->updateHallStatus($booking->hall_id);

        return response()->json([
            'message' => 'Hall booking confirmed successfully',
            'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
        ]);
    }

    /**
     * Check in / Start event for hall booking.
     */
    public function checkIn($id)
    {
        $booking = HallBooking::findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Only pending or confirmed bookings can be checked in'
            ], 400);
        }

        $booking->update(['status' => 'checked_in']);
        $this->updateHallStatus($booking->hall_id);

        return response()->json([
            'message' => 'Hall booking checked in successfully',
            'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
        ]);
    }

    /**
     * Cancel a hall booking.
     */
    public function cancel($id)
    {
        $booking = HallBooking::findOrFail($id);

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot cancel ' . $booking->status . ' booking'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);
        $this->deleteBookingReceipts($booking);
        $this->updateHallStatus($booking->hall_id);

        return response()->json([
            'message' => 'Hall booking cancelled successfully',
            'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
        ]);
    }

    /**
     * Mark a hall booking as completed.
     * Flow: complete → hall status = 'dirty' → auto housekeeping task created.
     * Hall becomes 'available' again only after the housekeeping task is completed.
     */
    public function complete($id)
    {
        $booking = HallBooking::with('hall')->findOrFail($id);

        if (!in_array($booking->status, ['confirmed', 'checked_in'])) {
            return response()->json([
                'message' => 'Only confirmed or checked-in bookings can be completed'
            ], 400);
        }

        // Consolidated Payment: Delete any split/earlier payment entries for this hall booking
        Payment::where('hall_booking_id', $booking->id)->delete();

        $hallAmount = (float) $booking->total_amount;

        $restaurantCharges = (float) RestaurantOrder::where('hall_booking_id', $booking->id)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $laundryCharges = (float) LaundryOrder::where('hall_booking_id', $booking->id)
            ->where('status', 'delivered')
            ->sum('total_amount');

        // Create EXACTLY 1 single master payment record combining hall + restaurant + laundry charges
        Payment::create([
            'hall_booking_id'    => $booking->id,
            'payment_type'       => 'full',
            'payment_method'     => 'cash',
            'amount'             => $hallAmount,
            'restaurant_charges' => $restaurantCharges,
            'laundry_charges'    => $laundryCharges,
            'notes'              => 'Pelunasan gabungan saat Acara Selesai/Complete (Hall: Rp ' . number_format($hallAmount, 0, ',', '.') . ' + Restoran: Rp ' . number_format($restaurantCharges, 0, ',', '.') . ')',
            'processed_by'       => auth()->id(),
        ]);

        $booking->update(['status' => 'completed']);
        $this->deleteBookingReceipts($booking);

        // Set hall status to 'dirty' — pending housekeeping cleaning
        if ($booking->hall) {
            $booking->hall->update(['status' => 'dirty']);
        }

        // Auto-create a pending hall_cleaning housekeeping task
        \App\Models\HousekeepingTask::create([
            'hall_id'   => $booking->hall_id,
            'task_type' => 'hall_cleaning',
            'priority'  => 'high',
            'status'    => 'pending',
            'notes'     => 'Auto-generated: Pembersihan hall setelah acara "'
                           . $booking->event_name . '" ('
                           . $booking->booking_number . ')',
        ]);

        return response()->json([
            'message' => 'Hall booking completed. Status hall diset ke dirty, tugas kebersihan dibuat otomatis.',
            'booking' => $booking->load(['hall', 'guest', 'bookedBy'])
        ]);
    }


    /**
     * Get calendar view of hall bookings.
     */
    public function calendar(Request $request)
    {
        $query = HallBooking::with(['hall', 'guest'])
            ->whereNotIn('status', ['cancelled']);

        // Filter by date range
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('event_date', [$request->start, $request->end]);
        }

        // Filter by hall
        if ($request->has('hall_id')) {
            $query->where('hall_id', $request->hall_id);
        }

        $bookings = $query->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->event_name,
                'start' => $booking->event_date . ' ' . $booking->start_time,
                'end' => $booking->event_date . ' ' . $booking->end_time,
                'backgroundColor' => $this->getStatusColor($booking->status),
                'borderColor' => $this->getStatusColor($booking->status),
                'extendedProps' => [
                    'booking_number' => $booking->booking_number,
                    'hall_name' => $booking->hall->name,
                    'customer_name' => $booking->customer_name,
                    'attendees' => $booking->attendees,
                    'status' => $booking->status,
                ]
            ];
        });

        return response()->json($bookings);
    }

    /**
     * Public hall reservation from website landing page.
     */
    public function publicStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hall_id' => 'required|exists:halls,id',
            'customer_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_company' => 'nullable|string|max:100',
            'event_name' => 'required|string|max:200',
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'attendees' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
            'payment_option' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hall = Hall::findOrFail($request->hall_id);

        if ($request->attendees > $hall->capacity) {
            return response()->json([
                'message' => "Jumlah peserta ({$request->attendees}) melebihi kapasitas maksimum hall ini ({$hall->capacity} orang)."
            ], 422);
        }

        $startTime = date('H:i', strtotime($request->start_time));
        $endTime = date('H:i', strtotime($request->end_time));

        if (strtotime($endTime) <= strtotime($startTime)) {
            return response()->json([
                'message' => 'Waktu selesai harus lebih lambat dari waktu mulai.'
            ], 422);
        }

        $isAvailable = HallBooking::isAvailable(
            $request->hall_id,
            $request->event_date,
            $startTime,
            $endTime
        );

        if (!$isAvailable) {
            return response()->json([
                'message' => "Maaf, Hall '{$hall->name}' sudah dipesan pada tanggal " . date('d/m/Y', strtotime($request->event_date)) . " jam {$startTime} - {$endTime}. Silakan pilih tanggal atau jam lainnya."
            ], 422);
        }

        DB::beginTransaction();
        try {
            $duration = HallBooking::calculateDuration($startTime, $endTime);
            $total = HallBooking::calculateTotal($request->hall_id, $duration);
            $isGuaranteed = in_array($request->payment_option, ['guaranteed', 'dp', 'transfer_dp']);
            $depositAmount = $isGuaranteed ? ($total * 0.5) : 0;

            $guest = \App\Models\Guest::where('email', $request->customer_email)
                ->orWhere('phone', $request->customer_phone)
                ->first();

            if (!$guest) {
                $guest = \App\Models\Guest::create([
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                ]);
            }

            $booking = HallBooking::create([
                'booking_number' => HallBooking::generateBookingNumber(),
                'hall_id' => $request->hall_id,
                'guest_id' => $guest->id,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_company' => $request->customer_company ?? null,
                'event_name' => $request->event_name,
                'event_date' => $request->event_date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_hours' => $duration,
                'attendees' => $request->attendees,
                'total_amount' => $total,
                'deposit_amount' => $depositAmount,
                'status' => 'pending',
                'special_requests' => $request->special_requests,
                'notes' => 'Pemesanan Hall via Website (' . ($request->payment_option ?? 'pay_at_hotel') . ')',
                'booked_by' => null,
            ]);

            if ($isGuaranteed) {
                Payment::create([
                    'hall_booking_id' => $booking->id,
                    'payment_type' => 'deposit',
                    'payment_method' => 'transfer',
                    'amount' => $depositAmount,
                    'notes' => 'Pemesanan Hall Jaminan via Website - Menunggu Verifikasi Staf',
                    'processed_by' => null,
                ]);
            }

            $this->updateHallStatus($booking->hall_id);

            DB::commit();

            return response()->json([
                'message' => 'Pemesanan Hall berhasil dikirim! Kode booking Anda adalah ' . $booking->booking_number,
                'data' => $booking->load(['hall', 'guest'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat pemesanan hall: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status color for calendar.
     */
    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => '#FFA500',
            'confirmed' => '#28A745',
            'completed' => '#6C757D',
            'cancelled' => '#DC3545',
            default => '#007BFF',
        };
    }

    /**
     * Helper to update physical hall status based on active bookings.
     * Does NOT override 'dirty' or 'cleaning' statuses — those are
     * managed by the housekeeping flow (task completion → available).
     */
    private function updateHallStatus($hallId)
    {
        $hall = Hall::find($hallId);
        if (!$hall) return;

        // Keep administrative / housekeeping statuses — do not override
        if (in_array($hall->status, ['maintenance', 'unavailable', 'dirty', 'cleaning'])) {
            return;
        }

        // Check if event is currently checked in → occupied
        $hasCheckedIn = HallBooking::where('hall_id', $hallId)
            ->where('status', 'checked_in')
            ->exists();

        if ($hasCheckedIn) {
            $hall->update(['status' => 'occupied']);
            return;
        }

        // No event currently checked in → available
        $hall->update(['status' => 'available']);
    }

    /**
     * Helper to delete physical receipt files associated with a hall booking.
     */
    private function deleteBookingReceipts($booking)
    {
        if (!$booking || !$booking->payments) return;

        foreach ($booking->payments as $payment) {
            if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
                Storage::disk('public')->delete($payment->receipt_path);
            }
            $payment->update(['receipt_path' => null]);
        }
    }
}

