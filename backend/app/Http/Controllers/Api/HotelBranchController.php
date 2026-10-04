<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HotelBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HotelBranchController extends Controller
{
    /**
     * Public index for Landing Page Branch Selector
     */
    public function publicIndex()
    {
        $branches = HotelBranch::where('is_active', true)
            ->select('id', 'name', 'slug', 'address', 'city', 'phone', 'email', 'description', 'image')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Display a listing of the branches for Admin Dashboard
     */
    public function index(Request $request)
    {
        $query = HotelBranch::withCount(['rooms', 'halls', 'bookings']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $branches = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:hotel_branches,slug',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $branch = HotelBranch::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cabang hotel berhasil ditambahkan',
            'data' => $branch,
        ], 201);
    }

    /**
     * Display the specified branch.
     */
    public function show($id)
    {
        $branch = HotelBranch::withCount(['rooms', 'halls', 'bookings'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $branch,
        ]);
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, $id)
    {
        $branch = HotelBranch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:hotel_branches,slug,' . $id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name']) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $branch->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data cabang hotel berhasil diperbarui',
            'data' => $branch,
        ]);
    }

    /**
     * Remove the specified branch.
     */
    public function destroy($id)
    {
        $branch = HotelBranch::findOrFail($id);
        $branch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cabang hotel berhasil dihapus',
        ]);
    }
}
