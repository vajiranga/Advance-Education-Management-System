<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Verify Super Admin Password before sensitive actions
    public function verifySuperAdmin(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 403);
        }

        return response()->json(['success' => true]);
    }

    public function index()
    {
        // Return all admins
        $admins = User::where('role', 'admin')->get();
        return response()->json($admins);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'permissions' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();

            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'plain_password' => $request->password,
                'role' => 'admin',
                'permissions' => $request->permissions ?? [],
                'is_super_admin' => false,
                'username' => 'ADM' . date('Y') . rand(100, 999)
            ]);

            DB::commit();
            return response()->json(['message' => 'Admin created successfully', 'admin' => $admin]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,'.$id,
            'permissions' => 'nullable|array'
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->permissions = $request->permissions;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
            $admin->plain_password = $request->password;
        }

        $admin->save();

        return response()->json(['message' => 'Admin updated successfully', 'admin' => $admin]);
    }

    public function destroy($id)
    {
        $admin = User::findOrFail($id);

        // Prevent deleting self or Super Admin
        if ($admin->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 403);
        }

        if ($admin->is_super_admin) {
             return response()->json(['message' => 'Cannot delete Super Admin'], 403);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
