<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\MenuItem;
use App\Exports\RestaurantOrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = RestaurantOrder::with(['booking.guest', 'booking.rooms', 'hallBooking.hall', 'orderItems.menuItem', 'createdBy']);

        // Filter by booking
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        // Filter by hall booking
        if ($request->filled('hall_booking_id')) {
            $query->where('hall_booking_id', $request->hall_booking_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'hall_booking_id' => 'nullable|exists:hall_bookings,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
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

        DB::beginTransaction();
        try {
            // Create order
            $order = RestaurantOrder::create([
                'booking_id' => $validated['booking_id'] ?? null,
                'hall_booking_id' => $validated['hall_booking_id'] ?? null,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            // Create order items
            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $quantity = $item['quantity'];
                $price = $menuItem->price;
                $subtotal = $price * $quantity;

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            // Load appropriate relationships based on booking type
            if ($order->booking_id) {
                $order->load(['booking.guest', 'booking.rooms', 'orderItems.menuItem']);
            } else {
                $order->load(['hallBooking.hall', 'orderItems.menuItem']);
            }

            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    public function show(RestaurantOrder $restaurantOrder)
    {
        if ($restaurantOrder->booking_id) {
            $restaurantOrder->load(['booking.guest', 'booking.rooms', 'orderItems.menuItem', 'createdBy']);
        } else {
            $restaurantOrder->load(['hallBooking.hall', 'orderItems.menuItem', 'createdBy']);
        }
        return response()->json($restaurantOrder);
    }

    public function updateStatus(Request $request, RestaurantOrder $restaurantOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,delivered,cancelled',
        ]);

        $restaurantOrder->update($validated);
        $restaurantOrder->load(['booking.guest', 'booking.rooms', 'orderItems.menuItem']);

        return response()->json($restaurantOrder);
    }

    public function destroy(RestaurantOrder $restaurantOrder)
    {
        // Can only delete pending orders
        if ($restaurantOrder->status !== 'pending') {
            return response()->json(['message' => 'Can only delete pending orders'], 422);
        }

        $restaurantOrder->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    /**
     * Get total restaurant charges for a booking (for auto-fill in payment)
     */
    public function getBookingCharges($bookingId)
    {
        $total = RestaurantOrder::where('booking_id', $bookingId)
            ->whereIn('status', ['pending', 'preparing', 'delivered'])
            ->sum('total_amount');

        return response()->json([
            'booking_id' => $bookingId,
            'restaurant_charges' => $total
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        $filename = 'restaurant_orders_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new RestaurantOrdersExport($startDate, $endDate, $status),
            $filename
        );
    }
}
