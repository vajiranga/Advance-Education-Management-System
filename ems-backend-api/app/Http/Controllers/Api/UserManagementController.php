<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%") // For Index Number
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }


    public function store(Request $request)
    {
        // Reusing similar validation logic but as admin
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,teacher,parent',
            'email' => $request->role === 'teacher' ? 'required|string|email|max:255|unique:users' : 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', // Admin sets password directly
            
            // Optional fields
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'school' => 'nullable|string',
            'grade' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'nic' => 'nullable|string',
            'experience' => 'nullable|string',
            'qualifications' => 'nullable|array',
            'subjects' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $username = null;
        $prefix = '';
        if ($request->role === 'student') $prefix = 'STU';
        elseif ($request->role === 'teacher') $prefix = 'TCH';
        elseif ($request->role === 'parent') $prefix = 'PAR';

        if ($prefix) {
             do {
                $username = $prefix . date('Y') . rand(1000, 9999);
             } while (User::where('username', $username)->exists());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => $request->role,
            'username' => $username,
            
            'dob' => $request->dob,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'whatsapp' => $request->whatsapp,
            'school' => $request->school,
            'grade' => $request->grade,
            
            'parent_name' => $request->parent_name,
            'parent_phone' => $request->parent_phone,
            'parent_email' => $request->parent_email,

            'nic' => $request->nic,
            'qualifications' => $request->qualifications,
            'subjects' => $request->subjects,
            'experience' => $request->experience,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,'.$id,
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->fill($request->except(['password', 'username', 'role'])); // Prevent role/username change for now if risky

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->plain_password = $request->password;
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
