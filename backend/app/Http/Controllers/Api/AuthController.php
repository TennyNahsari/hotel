<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = mb_strtolower(trim($request->email));

        $userExists = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$userExists) {
            throw ValidationException::withMessages([
                'email' => ['Email user tidak terdaftar pada sistem.'],
            ]);
        }

        if (Auth::attempt(['email' => $userExists->email, 'password' => $request->password])) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => ['Akun Anda dalam status non-aktif.'],
                ]);
            }

            $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');
            $relations = $hasBranchCol ? ['role', 'hotelBranch'] : ['role'];
            $user->load($relations);

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id,
                    'role' => $user->role,
                    'hotel_branch_id' => $hasBranchCol ? $user->hotel_branch_id : null,
                    'hotel_branch' => $hasBranchCol ? $user->hotelBranch : null,
                    'is_global' => $user->is_global,
                ],
            ]);
        }

        throw ValidationException::withMessages([
            'password' => ['Password yang Anda masukkan salah.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $hasBranchCol = Schema::hasColumn('users', 'hotel_branch_id');
        $relations = $hasBranchCol ? ['role', 'hotelBranch'] : ['role'];
        $user->load($relations);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_id' => $user->role_id,
                'role' => $user->role,
                'hotel_branch_id' => $hasBranchCol ? $user->hotel_branch_id : null,
                'hotel_branch' => $hasBranchCol ? $user->hotelBranch : null,
                'is_global' => $user->is_global,
            ],
        ]);
    }
}
