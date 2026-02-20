<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomType::withCount('rooms');

        // Only active room types by default
        if (!$request->has('include_inactive')) {
            $query->where('is_active', true);
        }

        $roomTypes = $query->orderBy('name')->get();

        return response()->json($roomTypes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:room_types,name',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'weekend_price' => 'nullable|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|array',
        ]);

        // Set weekend price to base price if not provided
        if (!isset($validated['weekend_price'])) {
            $validated['weekend_price'] = $validated['base_price'];
        }

        $roomType = RoomType::create($validated);

        return response()->json([
            'message' => 'Room type created successfully',
            'data' => $roomType->loadCount('rooms')
        ], 201);
    }

    public function show(RoomType $roomType)
    {
        return response()->json($roomType->loadCount('rooms'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100|unique:room_types,name,' . $roomType->id,
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0',
            'weekend_price' => 'nullable|numeric|min:0',
            'capacity' => 'sometimes|integer|min:1',
            'facilities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $roomType->update($validated);

        return response()->json([
            'message' => 'Room type updated successfully',
            'data' => $roomType->loadCount('rooms')
        ]);
    }

    public function destroy(RoomType $roomType)
    {
        // Check if room type has active rooms
        $activeRoomsCount = $roomType->rooms()->where('is_active', true)->count();
        
        if ($activeRoomsCount > 0) {
            return response()->json([
                'message' => "Cannot delete room type. It has {$activeRoomsCount} active room(s).",
                'has_active_rooms' => true
            ], 422);
        }

        // Soft delete by setting is_active to false
        $roomType->update(['is_active' => false]);

        return response()->json([
            'message' => 'Room type deactivated successfully'
        ]);
    }
}
