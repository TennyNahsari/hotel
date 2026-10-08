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

        $branchId = $request->get('hotel_branch_id', $request->header('X-Branch-ID'));
        if ($branchId) {
            $query->forBranch($branchId);
        }

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
            'hotel_branch_id' => 'nullable|exists:hotel_branches,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|array',
            'image_url' => 'nullable|string',
        ]);

        if (empty($validated['hotel_branch_id'])) {
            $validated['hotel_branch_id'] = $request->header('X-Branch-ID', 1);
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
            'capacity' => 'sometimes|integer|min:1',
            'facilities' => 'nullable|array',
            'image_url' => 'nullable|string',
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

    /**
     * Upload Room Type Image File
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = 'room_type_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('room_types', $filename, 'public');

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar tipe kamar berhasil diunggah',
            'image_path' => $path,
            'image_url' => asset('storage/' . $path)
        ]);
    }
}
