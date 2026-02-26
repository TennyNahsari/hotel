<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaundryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = LaundryOrder::with(['booking.guest', 'createdBy']);

        // Filter by booking
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Search by order number or booking number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('booking', function($q) use ($search) {
                      $q->where('booking_number', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'weight_kg' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total
            $totalAmount = $validated['weight_kg'] * $validated['price_per_kg'];

            // Create laundry order
            $laundryOrder = LaundryOrder::create([
                'booking_id' => $validated['booking_id'],
                'weight_kg' => $validated['weight_kg'],
                'price_per_kg' => $validated['price_per_kg'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create payment with laundry charges
            $payment = Payment::create([
                'booking_id' => $validated['booking_id'],
                'payment_type' => 'partial',
                'payment_method' => 'cash',
                'amount' => 0,
                'laundry_charges' => $totalAmount,
                'notes' => 'Laundry service - ' . $laundryOrder->order_number,
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Laundry order created successfully',
                'data' => $laundryOrder->load(['booking.guest', 'createdBy']),
                'payment' => $payment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create laundry order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(LaundryOrder $laundryOrder)
    {
        return response()->json(
            $laundryOrder->load(['booking.guest', 'createdBy'])
        );
    }

    public function destroy(LaundryOrder $laundryOrder)
    {
        $laundryOrder->delete();

        return response()->json([
            'message' => 'Laundry order deleted successfully'
        ]);
    }

    public function getBookingCharges($bookingId)
    {
        $total = LaundryOrder::where('booking_id', $bookingId)->sum('total_amount');

        return response()->json([
            'booking_id' => $bookingId,
            'laundry_charges' => $total
        ]);
    }
}
