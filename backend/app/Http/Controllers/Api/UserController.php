<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');
        $query = $hasBranchCol ? User::with(['role', 'hotelBranch']) : User::with(['role']);

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
        if ($hasBranchCol && $request->filled('hotel_branch_id')) {
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
        try {
            $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');

            if ($request->hotel_branch_id === '' || $request->hotel_branch_id === 'global') {
                $request->merge(['hotel_branch_id' => null]);
            }
            if ($request->phone === '') {
                $request->merge(['phone' => null]);
            }

            $rules = [
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:6',
                'role_id' => 'required|exists:roles,id',
                'is_active' => 'boolean',
            ];

            if ($hasBranchCol) {
                $rules['hotel_branch_id'] = 'nullable|exists:hotel_branches,id';
            }

            $validated = $request->validate($rules);

            if (!$hasBranchCol) {
                unset($validated['hotel_branch_id']);
            }

            $validated['password'] = Hash::make($validated['password']);
            if (!isset($validated['is_active'])) {
                $validated['is_active'] = true;
            }

            $user = User::create($validated);
            $relations = $hasBranchCol ? ['role', 'hotelBranch'] : ['role'];

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load($relations),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membuat user: ' . $e->getMessage()
            ], 422);
        }
    }

    public function show($id)
    {
        $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');
        $user = ($id instanceof User) ? $id : User::findOrFail($id);
        $relations = $hasBranchCol ? ['role', 'hotelBranch'] : ['role'];
        $user->load($relations);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        try {
            $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');
            $user = ($id instanceof User) ? $id : User::findOrFail($id);

            if ($request->hotel_branch_id === '' || $request->hotel_branch_id === 'global') {
                $request->merge(['hotel_branch_id' => null]);
            }
            if ($request->phone === '') {
                $request->merge(['phone' => null]);
            }
            if ($request->has('password') && ($request->password === '' || is_null($request->password))) {
                $request->offsetUnset('password');
            }

            $rules = [
                'name' => 'sometimes|required|string|max:100',
                'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:6',
                'role_id' => 'sometimes|required|exists:roles,id',
                'is_active' => 'boolean',
            ];

            if ($hasBranchCol) {
                $rules['hotel_branch_id'] = 'nullable|exists:hotel_branches,id';
            }

            $validated = $request->validate($rules);

            if (!$hasBranchCol) {
                unset($validated['hotel_branch_id']);
            }

            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);
            $relations = $hasBranchCol ? ['role', 'hotelBranch'] : ['role'];

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->load($relations),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
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
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getRoles()
    {
        $roles = Role::orderBy('id', 'asc')->get();
        return response()->json($roles);
    }
}
