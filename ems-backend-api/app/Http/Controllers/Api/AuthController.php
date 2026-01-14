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

        // Generate Username / Index Number
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
            'plain_password' => $request->password, // DEMO ONLY: Storing plain password as requested
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

        // Auto-create Parent User if this is a Student registration
        if ($request->role === 'student' && $request->parent_name) {
            $parent = User::where('role', 'parent')
                ->where(function($q) use ($request) {
                    if ($request->parent_phone) $q->orWhere('phone', $request->parent_phone);
                    if ($request->parent_email) $q->orWhere('email', $request->parent_email);
                })->first();

            if (!$parent) {
                // Generate a placeholder email if none provided, using phone
                $parentEmail = $request->parent_email;
                if (empty($parentEmail) && $request->parent_phone) {
                    $parentEmail = $request->parent_phone . '@parent.ems';
                }

                User::create([
                    'name' => $request->parent_name,
                    'email' => $parentEmail,
                    'phone' => $request->parent_phone,
                    'role' => 'parent',
                    // Set a default password for the parent account
                    'password' => Hash::make('password123'), 
                    'plain_password' => 'password123',
                    'whatsapp' => $request->parent_phone, 
                    // Store the student connection? Not easy without pivot table, 
                    // but we can at least ensure the parent exists in the system.
                ]);
            }
        }

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

    public function parentLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'student_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find Student
        $student = User::where('username', $request->student_id)
                       ->where('role', 'student')
                       ->first();

        if (!$student) {
            return response()->json(['message' => 'Invalid Student ID'], 404);
        }

        // Validate Phone (Check against student's parent_phone)
        if ($student->parent_phone !== $request->phone) {
             return response()->json(['message' => 'Phone number does not match the registered parent phone for this student.'], 401);
        }

        // Find Parent User Account
        $parent = User::where('role', 'parent')
                      ->where('phone', $request->phone)
                      ->first();

        if (!$parent) {
             // Auto-create:
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
        // Format: PREFIX + Year + 4 Random Digits (e.g. STU20261234)
        do {
            $username = $prefix . date('Y') . rand(1000, 9999);
        } while (User::where('username', $username)->exists());
        
        return $username;
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

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->whatsapp = $request->whatsapp;
        if ($request->email) $user->email = $request->email;

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->plain_password = $request->password;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
