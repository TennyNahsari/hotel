<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by availability
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $menuItems = $query->orderBy('category', 'asc')
                           ->orderBy('name', 'asc')
                           ->get();

        return response()->json($menuItems);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:food,beverage,snack',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_available' => 'boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menu-items', 'public');
            $validated['photo'] = $path;
        }

        $menuItem = MenuItem::create($validated);

        return response()->json($menuItem, 201);
    }

    public function show(MenuItem $menuItem)
    {
        return response()->json($menuItem);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|in:food,beverage,snack',
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_available' => 'boolean',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($menuItem->photo) {
                Storage::disk('public')->delete($menuItem->photo);
            }
            $path = $request->file('photo')->store('menu-items', 'public');
            $validated['photo'] = $path;
        }

        $menuItem->update($validated);

        return response()->json($menuItem);
    }

    public function destroy(MenuItem $menuItem)
    {
        // Delete photo
        if ($menuItem->photo) {
            Storage::disk('public')->delete($menuItem->photo);
        }

        $menuItem->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }
}
