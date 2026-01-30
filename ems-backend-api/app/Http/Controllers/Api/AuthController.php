<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => $request->role === 'teacher' ? 'required|string|email|max:255|unique:users' : 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,teacher,parent',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'school' => 'nullable|string',
            'grade' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'parent_email' => 'nullable|email',
            'nic' => 'nullable|string',
            'qualifications' => 'nullable|array',
            'subjects' => 'nullable|array',
            'experience' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $username = null;
        if ($request->role === 'student') {
            $username = $this->generateUsername('STU');
        } elseif ($request->role === 'teacher') {
            $username = $this->generateUsername('TCH');
        } elseif ($request->role === 'parent') {
            $username = $this->generateUsername('PAR');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        if ($request->role === 'student' && $request->parent_name) {
            $parent = User::where('role', 'parent')
                ->where(function($q) use ($request) {
                    if ($request->parent_phone) $q->orWhere('phone', $request->parent_phone);
                    if ($request->parent_email) $q->orWhere('email', $request->parent_email);
                })->first();

            if (!$parent) {
                $parentEmail = $request->parent_email;
                if (empty($parentEmail) && $request->parent_phone) {
                    $parentEmail = $request->parent_phone . '@parent.ems';
                }

                User::create([
                    'name' => $request->parent_name,
                    'email' => $parentEmail,
                    'phone' => $request->parent_phone,
                    'role' => 'parent',
                    'password' => Hash::make('password123'),
                    'plain_password' => 'password123',
                    'whatsapp' => $request->parent_phone,
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'index_number' => $username,
            'token' => $token,
            'redirect_url' => $this->getRedirectUrl($request->role)
        ], 201);
    }

    public function login(Request $request)
    {
        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'email' => 'required', // Can be email or username
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please enter email and password'], 422);
        }

        // 2. Determine if logging in via Email or Username
        $loginType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 3. Find User
        $user = User::where($loginType, $request->email)->first();

        // 4. Check Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials provided.'], 401);
        }

        // 5. Generate Token
        // Revoke old tokens if single session preferred (Optional)
        // $user->tokens()->delete(); 
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'redirect_url' => $this->getRedirectUrl($user->role)
        ]);
    }

    public function parentLogin(Request $request)
    {
        // NOTE: This endpoint expects 'phone' and 'student_id'.
        // If frontend uses Email/Password form, they should hits standard login() above.
        
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'student_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $student = User::where('username', $request->student_id)
                       ->where('role', 'student')
                       ->first();

        if (!$student) {
            return response()->json(['message' => 'Invalid Student ID'], 404);
        }

        if ($student->parent_phone !== $request->phone) {
             return response()->json(['message' => 'Phone number does not match registered parent'], 401);
        }

        $parent = User::where('role', 'parent')->where('phone', $request->phone)->first();

        if (!$parent) {
             $parent = User::create([
                 'name' => $student->parent_name ?? 'Parent',
                 'phone' => $request->phone,
                 'role' => 'parent',
                 'email' => $request->phone . '@parent.ems',
                 'password' => Hash::make(Str::random(16)),
                 'plain_password' => 'no_password_login',
                 'username' => 'PAR-' . rand(1000,9999)
             ]);
        }

        $token = $parent->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Parent login success',
            'user' => $parent,
            'token' => $token,
            'redirect_url' => $this->getRedirectUrl('parent')
        ]);
    }

    private function generateUsername($prefix)
    {
        do {
            $username = $prefix . date('Y') . rand(1000, 9999);
        } while (User::where('username', $username)->exists());
        return $username;
    }

    private function getRedirectUrl($role)
    {
        if ($role === 'admin' || $role === 'super_admin') return 'http://localhost:9001';
        $baseUrl = 'http://localhost:9002';
        switch ($role) {
            case 'student': return "$baseUrl/student/dashboard";
            case 'teacher': return "$baseUrl/teacher/dashboard";
            case 'parent': return "$baseUrl/parent/dashboard";
            default: return "$baseUrl/login";
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $user->name = $request->name;
        if ($request->email) $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->plain_password = $request->password;
        }
        $user->save();
        return response()->json(['message' => 'Updated', 'user' => $user]);
    }
}
