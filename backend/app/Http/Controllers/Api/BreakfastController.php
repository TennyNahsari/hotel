<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BreakfastController extends Controller
{
    /**
     * Get all bookings eligible for breakfast today
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        
        $query = Booking::with(['guest', 'room.roomType'])
            ->where('check_in_date', '<=', $today)
            ->where('check_out_date', '>', $today)
            ->whereIn('status', ['confirmed', 'checked_in']);

        // Filter by breakfast status
        if ($request->filled('breakfast_status')) {
            $query->where('breakfast_status', $request->breakfast_status);
        }

        // Search by guest name or room number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('guest', function($guestQuery) use ($search) {
                    $guestQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('room', function($roomQuery) use ($search) {
                    $roomQuery->where('room_number', 'like', "%{$search}%");
                });
            });
        }

        $bookings = $query->orderBy('room_id', 'asc')->paginate(15);

        return response()->json($bookings);
    }

    /**
     * Update breakfast status for a booking
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'breakfast_status' => 'required|in:not_taken,taken',
        ]);

        $booking->update([
            'breakfast_status' => $validated['breakfast_status'],
            'breakfast_date' => Carbon::today(),
        ]);

        $booking->load(['guest', 'room.roomType']);

        return response()->json([
            'message' => 'Breakfast status updated successfully',
            'booking' => $booking
        ]);
    }

    /**
     * Get breakfast statistics for today
     */
    public function statistics()
    {
        $today = Carbon::today();

        $total = Booking::where('check_in_date', '<=', $today)
            ->where('check_out_date', '>', $today)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        $taken = Booking::where('check_in_date', '<=', $today)
            ->where('check_out_date', '>', $today)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where('breakfast_status', 'taken')
            ->where('breakfast_date', $today)
            ->count();

        $notTaken = $total - $taken;

        // Total portions (2 per booking)
        $totalPortions = $total * 2;
        $takenPortions = $taken * 2;
        $remainingPortions = $notTaken * 2;

        return response()->json([
            'total_bookings' => $total,
            'taken' => $taken,
            'not_taken' => $notTaken,
            'total_portions' => $totalPortions,
            'taken_portions' => $takenPortions,
            'remaining_portions' => $remainingPortions,
        ]);
    }
}
