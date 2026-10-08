<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'hotelBranch']);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role if provided
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        } elseif ($request->filled('role')) {
            $query->whereHas('role', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by branch
        if ($request->filled('hotel_branch_id')) {
            if ($request->hotel_branch_id === 'global') {
                $query->whereNull('hotel_branch_id');
            } else {
                $query->where('hotel_branch_id', $request->hotel_branch_id);
            }
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->orderBy('name', 'asc')->get();

        return response()->json($users);
    }

    public function store(Request $request)
    {
        if ($request->hotel_branch_id === '' || $request->hotel_branch_id === 'global') {
            $request->merge(['hotel_branch_id' => null]);
        }
        if ($request->phone === '') {
            $request->merge(['phone' => null]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'hotel_branch_id' => 'nullable|exists:hotel_branches,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load(['role', 'hotelBranch']),
        ], 201);
    }

    public function show($id)
    {
        $user = ($id instanceof User) ? $id : User::findOrFail($id);
        $user->load(['role', 'hotelBranch']);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = ($id instanceof User) ? $id : User::findOrFail($id);

        if ($request->hotel_branch_id === '' || $request->hotel_branch_id === 'global') {
            $request->merge(['hotel_branch_id' => null]);
        }
        if ($request->phone === '') {
            $request->merge(['phone' => null]);
        }
        if ($request->has('password') && (is_null($request->password) || $request->password === '')) {
            $request->request->remove('password');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role_id' => 'sometimes|required|exists:roles,id',
            'hotel_branch_id' => 'nullable|exists:hotel_branches,id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['role', 'hotelBranch']),
        ]);
    }

    public function destroy($id)
    {
        $user = ($id instanceof User) ? $id : User::findOrFail($id);

        // Don't delete the logged-in user
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Tidak dapat menghapus akun Anda sendiri'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function getRoles()
    {
        $roles = Role::orderBy('id', 'asc')->get();
        return response()->json($roles);
    }
}
