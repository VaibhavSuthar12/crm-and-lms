<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles')->latest()->paginate(20);
        return response()->json($users);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        return response()->json([
            'message'   => 'User status updated.',
            'is_active' => $user->is_active,
        ]);
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:Admin,Sales Manager,Sales Executive',
        ]);

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'Role assigned.',
            'user'    => $user->load('roles'),
        ]);
    }
}
