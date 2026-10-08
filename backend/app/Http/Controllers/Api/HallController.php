<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HallController extends Controller
{
    public function publicIndex(Request $request)
    {
        $query = Hall::whereNotIn('status', ['maintenance', 'unavailable']);
        $branchId = $request->get('hotel_branch_id', $request->header('X-Branch-ID'));
        if ($branchId) {
            $query->forBranch($branchId);
        }
        $halls = $query->orderBy('id', 'asc')->get();
        return response()->json($halls);
    }

    /**
     * Display a listing of halls.
     */
    public function index(Request $request)
    {
        $query = Hall::query();

        $branchId = $request->get('hotel_branch_id', $request->header('X-Branch-ID'));
        if ($branchId) {
            $query->forBranch($branchId);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by hall type
        if ($request->filled('hall_type')) {
            $query->where('hall_type', $request->hall_type);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $halls = $query->paginate($request->get('per_page', 15));

        return response()->json($halls);
    }

    /**
     * Store a newly created hall.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_branch_id' => 'nullable|exists:hotel_branches,id',
            'name' => 'required|string|max:100',
            'hall_type' => 'required|string|max:50',
            'floor' => 'nullable|string|max:20',
            'capacity' => 'required|integer|min:1',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_hour' => 'required|numeric|min:0',
            'facilities' => 'nullable|json',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'status' => 'required|in:available,booked,occupied,maintenance,unavailable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (empty($data['hotel_branch_id'])) {
            $data['hotel_branch_id'] = $request->header('X-Branch-ID', 1);
        }

        $hall = Hall::create($data);

        return response()->json([
            'message' => 'Hall created successfully',
            'hall' => $hall
        ], 201);
    }

    /**
     * Display the specified hall.
     */
    public function show($id)
    {
        $hall = Hall::with(['bookings' => function ($query) {
            $query->whereNotIn('status', ['cancelled'])
                  ->orderBy('event_date', 'desc')
                  ->limit(10);
        }])->findOrFail($id);

        return response()->json($hall);
    }

    /**
     * Update the specified hall.
     */
    public function update(Request $request, $id)
    {
        $hall = Hall::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:halls,name,' . $id,
            'hall_type' => 'required|string|max:50',
            'floor' => 'nullable|string|max:20',
            'capacity' => 'required|integer|min:1',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_hour' => 'required|numeric|min:0',
            'facilities' => 'nullable|json',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'status' => 'required|in:available,booked,occupied,maintenance,unavailable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hall->update($request->all());

        return response()->json([
            'message' => 'Hall updated successfully',
            'hall' => $hall
        ]);
    }

    /**
     * Remove the specified hall.
     */
    public function destroy($id)
    {
        $hall = Hall::findOrFail($id);

        // Check if hall has any bookings
        if ($hall->bookings()->whereNotIn('status', ['cancelled'])->exists()) {
            return response()->json([
                'message' => 'Cannot delete hall with active bookings'
            ], 400);
        }

        $hall->delete();

        return response()->json([
            'message' => 'Hall deleted successfully'
        ]);
    }

    /**
     * Check availability of a hall for a specific date and time range.
     */
    public function checkAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $hall = Hall::findOrFail($id);

        $isAvailable = \App\Models\HallBooking::isAvailable(
            $id,
            $request->event_date,
            $request->start_time,
            $request->end_time
        );

        return response()->json([
            'available' => $isAvailable,
            'hall' => $hall
        ]);
    }

    /**
     * Get hall types for dropdown.
     */
    public function getTypes()
    {
        $types = [
            'Meeting Room Small',
            'Meeting Room Medium',
            'Conference Hall',
            'Ballroom',
            'Function Room',
        ];

        return response()->json($types);
    }

    /**
     * Upload Hall Image File
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = 'hall_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('halls', $filename, 'public');

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar hall berhasil diunggah',
            'image_path' => $path,
            'image_url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Delete Hall Image File
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_url' => 'required|string',
        ]);

        $url = $request->input('image_url');
        if (str_contains($url, 'storage/')) {
            $path = explode('storage/', $url)[1];
            $path = str_replace('..', '', $path);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berkas gambar berhasil dihapus'
        ]);
    }
}
