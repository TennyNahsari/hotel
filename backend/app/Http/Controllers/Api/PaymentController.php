<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking.guest', 'booking.rooms', 'hallBooking.hall', 'processedBy']);

        // Filter by booking
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by hall booking
        if ($request->filled('hall_booking_id')) {
            $query->where('hall_booking_id', $request->hall_booking_id);
        }

        // Filter by booking type (room / hall)
        if ($request->filled('booking_type')) {
            if ($request->booking_type === 'room') {
                $query->whereNotNull('booking_id')->whereNull('hall_booking_id');
            } elseif ($request->booking_type === 'hall') {
                $query->whereNotNull('hall_booking_id')->whereNull('booking_id');
            }
        }

        // Filter by parent booking status
        // For room bookings: checked_out | For hall bookings: complete
        if ($request->filled('booking_status')) {
            $status = $request->booking_status;
            if ($status === 'checked_out') {
                // Only room bookings that are checked_out
                $query->whereNotNull('booking_id')
                      ->whereNull('hall_booking_id')
                      ->whereHas('booking', function ($q) use ($status) {
                          $q->where('status', $status);
                      });
            } elseif ($status === 'complete') {
                // Only hall bookings that are complete
                $query->whereNotNull('hall_booking_id')
                      ->whereNull('booking_id')
                      ->whereHas('hallBooking', function ($q) use ($status) {
                          $q->where('status', $status);
                      });
            } elseif ($status === 'checkout_or_complete') {
                // Both: room checked_out OR hall complete
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNotNull('booking_id')
                            ->whereNull('hall_booking_id')
                            ->whereHas('booking', function ($b) {
                                $b->where('status', 'checked_out');
                            });
                    })->orWhere(function ($sub) {
                        $sub->whereNotNull('hall_booking_id')
                            ->whereNull('booking_id')
                            ->whereHas('hallBooking', function ($hb) {
                                $hb->where('status', 'complete');
                            });
                    });
                });
            }
        }

        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by payment number or guest name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', '%' . $search . '%')
                  ->orWhereHas('booking.guest', function ($b) use ($search) {
                      $b->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('hallBooking', function ($hb) use ($search) {
                      $hb->where('customer_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'hall_booking_id' => 'nullable|exists:hall_bookings,id',
            'payment_type' => 'required|in:deposit,partial,full,refund,extra_charge',
            'payment_method' => 'required|in:cash,transfer,qris,card,other',
            'amount' => 'required|numeric|min:0',
            'restaurant_charges' => 'nullable|numeric|min:0',
            'laundry_charges' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate that at least one booking type is provided
        if (empty($validated['booking_id']) && empty($validated['hall_booking_id'])) {
            return response()->json([
                'message' => 'Either booking_id or hall_booking_id is required'
            ], 422);
        }

        // Validate that only one booking type is provided
        if (!empty($validated['booking_id']) && !empty($validated['hall_booking_id'])) {
            return response()->json([
                'message' => 'Cannot specify both booking_id and hall_booking_id'
            ], 422);
        }

        $validated['processed_by'] = auth()->id();

        $payment = Payment::create($validated);
        
        // Load relationships based on booking type
        if ($payment->booking_id) {
            $payment->load(['booking.guest', 'booking.room', 'processedBy']);
        } else {
            $payment->load(['hallBooking.hall', 'processedBy']);
        }

        return response()->json($payment, 201);
    }

    public function show(Payment $payment)
    {
        if ($payment->booking_id) {
            $payment->load(['booking.guest', 'booking.rooms.roomType', 'processedBy']);
        } else {
            $payment->load(['hallBooking.hall', 'processedBy']);
        }
        return response()->json($payment);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_type' => 'sometimes|required|in:deposit,partial,full,refund,extra_charge',
            'payment_method' => 'sometimes|required|in:cash,transfer,qris,card,other',
            'amount' => 'sometimes|required|numeric|min:0',
            'restaurant_charges' => 'nullable|numeric|min:0',
            'laundry_charges' => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);
        $payment->load(['booking.guest', 'booking.room', 'processedBy']);

        return response()->json($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }

    public function bookingPayments($bookingId)
    {
        $booking = Booking::with(['guest', 'room.roomType', 'payments.processedBy'])
            ->findOrFail($bookingId);

        $totalPaid = $booking->payments->sum('amount');
        $totalAmount = $booking->subtotal;
        $balance = $totalAmount - $totalPaid;

        return response()->json([
            'booking' => $booking,
            'payments' => $booking->payments,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'balance' => $balance,
            ],
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentType = $request->input('payment_type');
        $paymentMethod = $request->input('payment_method');

        $filename = 'payments_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new PaymentsExport($startDate, $endDate, $paymentType, $paymentMethod),
            $filename
        );
    }
}
