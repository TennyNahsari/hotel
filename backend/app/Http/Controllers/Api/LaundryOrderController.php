<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaundryOrder;
use App\Models\Payment;
use App\Exports\LaundryOrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaundryOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = LaundryOrder::with(['booking.guest', 'hallBooking.hall', 'createdBy']);

        // Filter by booking
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by hall booking
        if ($request->filled('hall_booking_id')) {
            $query->where('hall_booking_id', $request->hall_booking_id);
        }

        // Search by order number, room booking number, or hall booking number
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(order_number) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('booking', function($bq) use ($search) {
                      $bq->whereRaw('LOWER(booking_number) LIKE ?', ["%{$search}%"]);
                  })
                  ->orWhereHas('hallBooking', function($hq) use ($search) {
                      $hq->whereRaw('LOWER(booking_number) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'hall_booking_id' => 'nullable|exists:hall_bookings,id',
            'weight_kg' => 'required|numeric|min:0.1',
            'price_per_kg' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['booking_id']) && empty($validated['hall_booking_id'])) {
            return response()->json([
                'message' => 'Either booking_id or hall_booking_id is required'
            ], 422);
        }

        if (!empty($validated['booking_id']) && !empty($validated['hall_booking_id'])) {
            return response()->json([
                'message' => 'Cannot specify both booking_id and hall_booking_id'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calculate total
            $totalAmount = $validated['weight_kg'] * $validated['price_per_kg'];

            // Create laundry order
            $laundryOrder = LaundryOrder::create([
                'booking_id' => $validated['booking_id'] ?? null,
                'hall_booking_id' => $validated['hall_booking_id'] ?? null,
                'weight_kg' => $validated['weight_kg'],
                'price_per_kg' => $validated['price_per_kg'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create payment with laundry charges
            $payment = Payment::create([
                'booking_id' => $validated['booking_id'] ?? null,
                'hall_booking_id' => $validated['hall_booking_id'] ?? null,
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
                'data' => $laundryOrder->load(['booking.guest', 'hallBooking.hall', 'createdBy']),
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
            $laundryOrder->load(['booking.guest', 'hallBooking.hall', 'createdBy'])
        );
    }

    public function destroy(LaundryOrder $laundryOrder)
    {
        $laundryOrder->delete();

        return response()->json([
            'message' => 'Laundry order deleted successfully'
        ]);
    }

    public function getBookingCharges(Request $request, $bookingId)
    {
        if ($request->get('type') === 'hall') {
            $total = LaundryOrder::where('hall_booking_id', $bookingId)->sum('total_amount');
            return response()->json([
                'hall_booking_id' => $bookingId,
                'laundry_charges' => $total
            ]);
        }

        $total = LaundryOrder::where('booking_id', $bookingId)->sum('total_amount');

        return response()->json([
            'booking_id' => $bookingId,
            'laundry_charges' => $total
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filename = 'laundry_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new LaundryOrdersExport($startDate, $endDate),
            $filename
        );
    }
}
