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
            // Email is optional for students but mandatory for teachers
            'email' => $request->role === 'teacher' ? 'required|string|email|max:255|unique:users' : 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // expects password_confirmation
            'role' => 'required|in:student,teacher,parent',
            
            // Student/Parent extra fields
            'dob' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'school' => 'nullable|string',
            'grade' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string', // Emergency
            'parent_email' => 'nullable|email',

            // Teacher extra fields
            'nic' => 'nullable|string', // Consider adding unique if strict
            'qualifications' => 'nullable|array',
            'subjects' => 'nullable|array',
            'experience' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generate Index Number for Students
        $username = null;
        if ($request->role === 'student') {
            $username = $this->generateIndexNumber();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'index_number' => $username, // Send this back so frontend can show it
            'token' => $token,
            'redirect_url' => $this->getRedirectUrl($request->role)
        ], 201);
    }

    public function login(Request $request)
    {
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->email, // The input field is named 'email' from frontend but holds identifier
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where($loginField, $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $role = $user->role;

        return response()->json([
            'message' => 'Login success',
            'user' => $user,
            'token' => $token,
            'redirect_url' => $this->getRedirectUrl($role)
        ]);
    }

    private function generateIndexNumber()
    {
        // Format: STU + Year + 4 Random Digits (e.g. STU20261234)
        // Check uniqueness
        do {
            $index = 'STU' . date('Y') . rand(1000, 9999);
        } while (User::where('username', $index)->exists());
        
        return $index;
    }

    private function getRedirectUrl($role)
    {
        $baseUrl = 'http://localhost:9001'; // Client App Port
        switch ($role) {
            case 'student': return "$baseUrl/student/dashboard";
            case 'teacher': return "$baseUrl/teacher/dashboard";
            case 'parent': return "$baseUrl/parent/dashboard";
            default: return "$baseUrl/login";
        }
    }
}
