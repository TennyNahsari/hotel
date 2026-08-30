<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Payment;
use App\Exports\BookingsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // Auto process overdue cancellations
        app(\App\Http\Controllers\Api\DashboardController::class)->processOverdueBookings();

        $query = Booking::with(['guest', 'rooms.roomType', 'payments', 'createdBy']);

        // Filter by status — use filled() so empty string doesn't wrongly filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by check-in date range
        if ($request->filled('start_date')) {
            $query->where('check_in_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('check_in_date', '<=', $request->end_date);
        }

        // Search by guest name, booking number, or room number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhereHas('guest', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('rooms', function ($q) use ($search) {
                      $q->where('room_number', 'like', "%{$search}%");
                  });
            });
        }

        $perPage  = $request->get('per_page', 15);
        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'integer|min:0',
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'exists:rooms,id',
            'source' => 'nullable|string',
            'status' => 'nullable|in:pending,confirmed,checked_in',
            'notes' => 'nullable|string',
            'special_requests' => 'nullable|string',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        // Calculate nights
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        // Check room availability
        foreach ($validated['room_ids'] as $roomId) {
            $isAvailable = $this->checkRoomAvailability(
                $roomId, 
                $validated['check_in_date'], 
                $validated['check_out_date']
            );
            
            if (!$isAvailable) {
                $room = Room::find($roomId);
                return response()->json([
                    'message' => "Room {$room->room_number} is not available for selected dates"
                ], 422);
            }
        }

        // Calculate total amount
        $totalAmount = 0;
        foreach ($validated['room_ids'] as $roomId) {
            $room = Room::with('roomType')->find($roomId);
            $totalAmount += $room->roomType->base_price * $nights;
        }

        $bookingStatus = $validated['status'] ?? 'pending';

        // Create booking
        $booking = Booking::create([
            'booking_number' => $this->generateBookingNumber(),
            'guest_id' => $validated['guest_id'],
            'created_by' => auth()->id(),
            'source' => $validated['source'] ?? 'walk_in',
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights' => $nights,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'status' => $bookingStatus,
            'total_amount' => $totalAmount,
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        // Attach rooms & update room status if confirmed or checked_in
        foreach ($validated['room_ids'] as $roomId) {
            $room = Room::with('roomType')->find($roomId);
            $booking->rooms()->attach($roomId, [
                'room_rate' => $room->roomType->base_price,
                'nights' => $nights,
                'subtotal' => $room->roomType->base_price * $nights,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ]);

            if (in_array($bookingStatus, ['pending', 'confirmed'])) {
                $room->update(['status' => 'booked']);
            } elseif ($bookingStatus === 'checked_in') {
                $room->update(['status' => 'occupied']);
            }
        }

        return response()->json([
            'message' => 'Booking created successfully',
            'data' => $booking->load(['guest', 'rooms.roomType', 'payments'])
        ], 201);
    }

    public function show(Booking $booking)
    {
        try {
            return response()->json(
                $booking->load(['guest', 'rooms.roomType', 'payments', 'createdBy'])
            );
        } catch (\Exception $e) {
            \Log::error('Failed to load booking: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load booking details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'check_in_date' => 'sometimes|date',
            'check_out_date' => 'sometimes|date|after:check_in_date',
            'adults' => 'sometimes|integer|min:1',
            'children' => 'integer|min:0',
            'notes' => 'nullable|string',
            'special_requests' => 'nullable|string',
        ]);

        // Recalculate nights if dates changed
        if (isset($validated['check_in_date']) || isset($validated['check_out_date'])) {
            $checkIn = Carbon::parse($validated['check_in_date'] ?? $booking->check_in_date);
            $checkOut = Carbon::parse($validated['check_out_date'] ?? $booking->check_out_date);
            $validated['nights'] = $checkIn->diffInDays($checkOut);
        }

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking->load(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    private function deleteBookingReceiptFiles(Booking $booking)
    {
        foreach ($booking->payments as $payment) {
            if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
                Storage::disk('public')->delete($payment->receipt_path);
            }
        }
    }

    public function destroy(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in', 'checked_out'])) {
            return response()->json([
                'message' => 'Cannot delete booking that has been checked in or out'
            ], 422);
        }

        // Delete associated receipt files from storage
        $this->deleteBookingReceiptFiles($booking);

        // Delete associated payments and detach rooms to avoid foreign key violation
        $booking->payments()->delete();
        $booking->rooms()->detach();

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully'
        ]);
    }

    public function confirm(Booking $booking)
    {
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Only pending or confirmed bookings can be confirmed'
            ], 422);
        }

        $booking->update(['status' => 'confirmed']);

        // Update assigned room(s) status to 'booked'
        foreach ($booking->rooms as $room) {
            $room->update(['status' => 'booked']);
        }

        return response()->json([
            'message' => 'Booking confirmed successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkIn(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return response()->json([
                'message' => 'Only confirmed bookings can be checked in'
            ], 422);
        }

        $booking->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        // Update room status to occupied
        foreach ($booking->rooms as $room) {
            $room->update(['status' => 'occupied']);
        }

        return response()->json([
            'message' => 'Booking checked in successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkOut(Booking $booking)
    {
        if ($booking->status !== 'checked_in') {
            return response()->json([
                'message' => 'Only checked-in bookings can be checked out'
            ], 422);
        }

        $booking->update([
            'status' => 'checked_out',
            'checked_out_at' => now(),
        ]);

        // Update room status to dirty
        foreach ($booking->rooms as $room) {
            $room->update(['status' => 'dirty']);
        }

        // Delete associated receipt files from storage upon checkout
        $this->deleteBookingReceiptFiles($booking);

        return response()->json([
            'message' => 'Booking checked out successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in', 'checked_out', 'cancelled'])) {
            return response()->json([
                'message' => 'Cannot cancel this booking'
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        // Revert assigned room(s) status back to available if booked
        foreach ($booking->rooms as $room) {
            if (in_array($room->status, ['booked', 'occupied'])) {
                $room->update(['status' => 'available']);
            }
        }

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking->fresh(['guest', 'rooms.roomType', 'payments'])
        ]);
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'room_type_id' => 'nullable|exists:room_types,id',
        ]);

        $query = Room::with('roomType')
            ->where('is_active', true)
            ->where('status', '!=', 'out_of_order');

        if (isset($validated['room_type_id'])) {
            $query->where('room_type_id', $validated['room_type_id']);
        }

        $availableRooms = $query->get()->filter(function($room) use ($validated) {
            return $this->checkRoomAvailability(
                $room->id, 
                $validated['check_in_date'], 
                $validated['check_out_date']
            );
        });

        return response()->json($availableRooms->values());
    }

    private function checkRoomAvailability($roomId, $checkIn, $checkOut)
    {
        $room = Room::find($roomId);
        if (!$room || !$room->is_active) {
            return false;
        }

        // If booking starts today or in the past, physical room status must be 'available'
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $today = now()->startOfDay();

        if ($checkInDate->lte($today)) {
            if ($room->status !== 'available') {
                return false;
            }
        } else {
            // For future dates, room must not be out of order
            if ($room->status === 'out_of_order') {
                return false;
            }
        }

        // Check date conflict with existing pending, confirmed, or checked_in bookings
        $hasConflict = Booking::whereHas('rooms', function($q) use ($roomId) {
            $q->where('room_id', $roomId);
        })
        ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
        ->where('check_in_date', '<', $checkOut)
        ->where('check_out_date', '>', $checkIn)
        ->exists();

        return !$hasConflict;
    }

    private function generateBookingNumber()
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "BK{$date}{$random}";
    }

    public function export(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'status', 'guest_id']);
        
        $filename = 'bookings_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new BookingsExport($filters), $filename);
    }

    public function publicStore(Request $request)
    {
        if ($request->has('phone')) {
            $request->merge([
                'phone' => preg_replace('/[^0-9]/', '', $request->input('phone', ''))
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|regex:/^[0-9]+$/|max:30',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'room_type_id' => 'nullable|exists:room_types,id',
            'special_requests' => 'nullable|string|max:1000',
            'payment_option' => 'required|in:pay_at_hotel,transfer_guaranteed',
            'bank_name' => 'nullable|string|max:50',
            'sender_name' => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
        ], [
            'phone.regex' => 'Nomor WhatsApp / HP hanya boleh berisi karakter angka.'
        ]);

        // Find an available room matching room_type_id or any room
        $roomQuery = Room::with('roomType')->where('is_active', true)->where('status', '!=', 'out_of_order');
        if (!empty($validated['room_type_id'])) {
            $roomQuery->where('room_type_id', $validated['room_type_id']);
        }

        $allRooms = $roomQuery->get();
        $selectedRoom = null;
        foreach ($allRooms as $room) {
            if ($this->checkRoomAvailability($room->id, $validated['check_in_date'], $validated['check_out_date'])) {
                $selectedRoom = $room;
                break;
            }
        }

        if (!$selectedRoom) {
            $typeName = 'yang dipilih';
            if (!empty($validated['room_type_id'])) {
                $roomTypeObj = \App\Models\RoomType::find($validated['room_type_id']);
                if ($roomTypeObj) {
                    $typeName = "tipe '" . $roomTypeObj->name . "'";
                }
            }
            return response()->json([
                'message' => "Maaf, kamar {$typeName} tidak tersedia (penuh/terisi) pada tanggal " . 
                             date('d/m/Y', strtotime($validated['check_in_date'])) . " s/d " . 
                             date('d/m/Y', strtotime($validated['check_out_date'])) . ". Silakan pilih tanggal atau tipe kamar lainnya."
            ], 422);
        }

        // Calculate nights
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = max(1, $checkIn->diffInDays($checkOut));

        $rate = $selectedRoom && $selectedRoom->roomType ? $selectedRoom->roomType->base_price : 280;
        $totalAmount = $rate * $nights;

        $isGuaranteed = $validated['payment_option'] === 'transfer_guaranteed';
        $depositAmount = $isGuaranteed ? round($totalAmount * 0.5, 2) : 0;

        $refInput = trim($validated['reference_number'] ?? '');
        $bankInput = trim($validated['bank_name'] ?? '');
        $senderInput = trim($validated['sender_name'] ?? '');

        $refParts = [];
        if ($bankInput) $refParts[] = strtoupper($bankInput);
        if ($senderInput) $refParts[] = "a/n " . $senderInput;
        if ($refInput) $refParts[] = "No. Ref: " . $refInput;

        $finalRef = count($refParts) > 0 ? implode(' - ', $refParts) : ('REF' . strtoupper(Str::random(6)));

        $paymentNotes = $isGuaranteed
            ? "Jaminan Transfer Bank: " . $finalRef
            : "Bayar di Hotel saat check-in (Kamar ditahan hingga jam 18:00 sore hari check-in).";

        $inputName = trim($validated['name']);
        $inputPhone = trim($validated['phone']);

        // Check if a pending booking exists for guest with exact case-insensitive Full Name and exact Whatsapp Number
        $existingBooking = Booking::where('status', 'pending')
            ->whereHas('guest', function($q) use ($inputName, $inputPhone) {
                $q->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($inputName)])
                  ->whereRaw('TRIM(phone) = ?', [$inputPhone]);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingBooking) {
            // Group into existing booking
            $existingBooking->rooms()->attach($selectedRoom->id, [
                'room_rate' => $rate,
                'nights' => $nights,
                'subtotal' => $totalAmount,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ]);
            $selectedRoom->update(['status' => 'booked']);

            // Update parent check-in and check-out date boundaries
            $curCheckIn = is_string($existingBooking->check_in_date) ? $existingBooking->check_in_date : ($existingBooking->check_in_date ? $existingBooking->check_in_date->format('Y-m-d') : $validated['check_in_date']);
            $curCheckOut = is_string($existingBooking->check_out_date) ? $existingBooking->check_out_date : ($existingBooking->check_out_date ? $existingBooking->check_out_date->format('Y-m-d') : $validated['check_out_date']);

            $minCheckIn = min($curCheckIn, $validated['check_in_date']);
            $maxCheckOut = max($curCheckOut, $validated['check_out_date']);

            $existingBooking->check_in_date = $minCheckIn;
            $existingBooking->check_out_date = $maxCheckOut;
            $existingBooking->nights = max(1, Carbon::parse($minCheckIn)->diffInDays(Carbon::parse($maxCheckOut)));

            $existingBooking->total_amount = (float)$existingBooking->total_amount + $totalAmount;
            $existingBooking->deposit_amount = (float)$existingBooking->deposit_amount + $depositAmount;
            $existingBooking->adults += $validated['adults'];
            $existingBooking->children += ($validated['children'] ?? 0);

            if (!empty($validated['special_requests'])) {
                $existingBooking->special_requests = trim(($existingBooking->special_requests ? $existingBooking->special_requests . " | " : "") . $validated['special_requests']);
            }

            $existingBooking->save();

            if ($isGuaranteed) {
                Payment::create([
                    'booking_id' => $existingBooking->id,
                    'payment_type' => 'deposit',
                    'payment_method' => 'transfer',
                    'amount' => $depositAmount,
                    'reference_number' => $finalRef,
                    'notes' => 'Pemesanan Kamar Tambahan via Website (' . $finalRef . ') - Menunggu Verifikasi Staf',
                    'processed_by' => null,
                ]);
            }

            $updatedBooking = $existingBooking->fresh(['guest', 'rooms.roomType', 'payments']);

            return response()->json([
                'message' => 'Kamar berhasil ditambahkan ke booking Anda! Kode booking Anda adalah ' . $updatedBooking->booking_number,
                'booking_number' => $updatedBooking->booking_number,
                'payment_option' => $validated['payment_option'],
                'deposit_amount' => $updatedBooking->deposit_amount,
                'data' => $updatedBooking
            ], 201);
        }

        // Find or create guest if creating new booking
        $guest = Guest::where('email', $validated['email'])
            ->orWhere('phone', $validated['phone'])
            ->first();

        if (!$guest) {
            $guest = Guest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        }

        $combinedNotes = trim($paymentNotes . " " . ($validated['special_requests'] ?? ''));

        $booking = Booking::create([
            'booking_number' => $this->generateBookingNumber(),
            'guest_id' => $guest->id,
            'created_by' => null,
            'source' => 'website',
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'nights' => $nights,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'status' => 'pending',
            'total_amount' => $totalAmount,
            'deposit_amount' => $depositAmount,
            'special_requests' => $validated['special_requests'] ?? null,
            'notes' => $combinedNotes,
        ]);

        if ($selectedRoom) {
            $booking->rooms()->attach($selectedRoom->id, [
                'room_rate' => $rate,
                'nights' => $nights,
                'subtotal' => $totalAmount,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ]);
            $selectedRoom->update(['status' => 'booked']);
        }

        // If guaranteed, log deposit payment entry for staff verification
        if ($isGuaranteed) {
            Payment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'deposit',
                'payment_method' => 'transfer',
                'amount' => $depositAmount,
                'reference_number' => $finalRef,
                'notes' => 'Pemesanan Jaminan via Website (' . $finalRef . ') - Menunggu Verifikasi Staf',
                'processed_by' => null,
            ]);
        }

        return response()->json([
            'message' => 'Pemesanan kamar berhasil dikirim! Kode booking Anda adalah ' . $booking->booking_number,
            'booking_number' => $booking->booking_number,
            'payment_option' => $validated['payment_option'],
            'deposit_amount' => $depositAmount,
            'data' => $booking->load(['guest', 'rooms.roomType', 'payments'])
        ], 201);
    }

    public function publicSearch(Request $request)
    {
        $validated = $request->validate([
            'booking_number' => 'required|string',
            'contact' => 'required|string',
        ]);

        $bookingNumber = trim($validated['booking_number']);
        $contact = trim($validated['contact']);

        // Check if it's a hall booking (HB-...)
        if (str_starts_with(strtoupper($bookingNumber), 'HB-')) {
            $hallBooking = \App\Models\HallBooking::with(['hall', 'guest', 'payments'])
                ->where('booking_number', $bookingNumber)
                ->where(function($q) use ($contact) {
                    $q->where('customer_email', $contact)
                      ->orWhere('customer_phone', $contact)
                      ->orWhereHas('guest', function($g) use ($contact) {
                          $g->where('email', $contact)->orWhere('phone', $contact);
                      });
                })
                ->first();

            if ($hallBooking) {
                $eventDateStr = is_string($hallBooking->event_date) ? substr($hallBooking->event_date, 0, 10) : $hallBooking->event_date->format('Y-m-d');
                $dueAt = $hallBooking->payment_due_at ? $hallBooking->payment_due_at->toIso8601String() : null;
                $isOverdue = $hallBooking->status === 'pending' && $hallBooking->payment_due_at && now()->gt($hallBooking->payment_due_at);

                return response()->json([
                    'data' => [
                        'id' => $hallBooking->id,
                        'booking_number' => $hallBooking->booking_number,
                        'booking_type' => 'hall',
                        'status' => $hallBooking->status,
                        'event_name' => $hallBooking->event_name,
                        'hall_name' => $hallBooking->hall ? $hallBooking->hall->name : '-',
                        'check_in_date' => $eventDateStr . ' (' . $hallBooking->start_time . ')',
                        'check_out_date' => $eventDateStr . ' (' . $hallBooking->end_time . ')',
                        'total_amount' => (float)$hallBooking->total_amount,
                        'deposit_amount' => 0,
                        'source' => 'Website (Hall)',
                        'payment_due_at' => $dueAt,
                        'is_overdue' => $isOverdue,
                        'guest' => [
                            'name' => $hallBooking->customer_name,
                            'email' => $hallBooking->customer_email,
                            'phone' => $hallBooking->customer_phone,
                        ],
                        'payments' => $hallBooking->payments
                    ]
                ]);
            }
        }

        $booking = Booking::with(['guest', 'rooms.roomType', 'payments'])
            ->where('booking_number', $bookingNumber)
            ->whereHas('guest', function($q) use ($contact) {
                $q->where('email', $contact)
                  ->orWhere('phone', $contact);
            })
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Pemesanan tidak ditemukan. Harap periksa kembali Nomor Kode Booking dan Email/No. HP Anda.'
            ], 404);
        }

        $bookingData = $booking->toArray();
        $bookingData['booking_type'] = 'room';
        $bookingData['payment_due_at'] = $booking->payment_due_at ? $booking->payment_due_at->toIso8601String() : null;
        $bookingData['is_overdue'] = $booking->status === 'pending' && $booking->payment_due_at && now()->gt($booking->payment_due_at);

        return response()->json([
            'data' => $bookingData
        ]);
    }

    public function uploadReceipt(Request $request)
    {
        $validated = $request->validate([
            'booking_number' => 'required|string',
            'contact' => 'nullable|string',
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        $bookingNumber = trim($validated['booking_number']);
        $contact = trim($validated['contact'] ?? '');

        // 1. Check Hall Bookings first
        $hallBooking = \App\Models\HallBooking::where('booking_number', $bookingNumber)->first();
        if ($hallBooking) {
            $file = $request->file('receipt');
            $filename = 'receipt_' . $hallBooking->booking_number . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('receipts', $filename, 'public');

            $payment = Payment::where('hall_booking_id', $hallBooking->id)->first();
            if ($payment) {
                if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
                    Storage::disk('public')->delete($payment->receipt_path);
                }
                $payment->update(['receipt_path' => $path]);
            } else {
                $payment = Payment::create([
                    'booking_id' => null,
                    'hall_booking_id' => $hallBooking->id,
                    'payment_type' => 'deposit',
                    'payment_method' => 'transfer',
                    'amount' => round($hallBooking->total_amount * 0.5, 2),
                    'reference_number' => 'UP-' . strtoupper(Str::random(6)),
                    'receipt_path' => $path,
                    'notes' => 'Bukti Transfer Hall diunggah oleh Tamu via Website',
                ]);
            }

            return response()->json([
                'message' => 'Bukti transfer hall berhasil diunggah! Tim concierge hotel kami akan memverifikasi pembayaran Anda.',
                'receipt_path' => $path,
                'receipt_url' => asset('storage/' . $path),
                'payment' => $payment
            ]);
        }

        // 2. Check Room Bookings
        $bookingQuery = Booking::where('booking_number', $bookingNumber);
        if (!empty($contact)) {
            $bookingQuery->whereHas('guest', function($q) use ($contact) {
                $q->where('email', $contact)->orWhere('phone', $contact);
            });
        }
        $booking = $bookingQuery->first();

        if (!$booking) {
            $booking = Booking::where('booking_number', $bookingNumber)->first();
        }

        if (!$booking) {
            return response()->json(['message' => 'Pemesanan tidak ditemukan. Periksa kembali Nomor Kode Booking Anda.'], 404);
        }

        $file = $request->file('receipt');
        $filename = 'receipt_' . $booking->booking_number . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('receipts', $filename, 'public');

        $payment = Payment::where('booking_id', $booking->id)->first();
        if ($payment) {
            if ($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path)) {
                Storage::disk('public')->delete($payment->receipt_path);
            }
            $payment->update(['receipt_path' => $path]);
        } else {
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_type' => 'deposit',
                'payment_method' => 'transfer',
                'amount' => round($booking->total_amount * 0.5, 2),
                'reference_number' => 'UP-' . strtoupper(Str::random(6)),
                'receipt_path' => $path,
                'notes' => 'Bukti Transfer diunggah oleh Tamu via Website',
            ]);
        }

        return response()->json([
            'message' => 'Bukti transfer berhasil diunggah! Tim concierge hotel kami akan memverifikasi pembayaran Anda.',
            'receipt_path' => $path,
            'receipt_url' => asset('storage/' . $path),
            'payment' => $payment
        ]);
    }
}
