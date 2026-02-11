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
    use \App\Traits\GeneratesCustomIds;

    public function register(Request $request)
    {
        // --- MAINTENANCE MODE CHECK ---
        $maintenanceSetting = \App\Models\SystemSetting::where('key', 'maintenanceMode')->first();
        if ($maintenanceSetting && ($maintenanceSetting->value === 'true' || $maintenanceSetting->value === '1' || $maintenanceSetting->value === 1 || $maintenanceSetting->value === true)) {
             return response()->json([
                 'message' => 'System is currently under maintenance. Please try again later.',
                 'maintenance_mode' => true
             ], 503);
        }
        // ------------------------------

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

        // Custom ID Generation Logic
        $prefix = 'STU';
        $startSequence = 1;

        if ($request->role === 'student') {
            $prefix = \App\Models\SystemSetting::where('key', 'studentIdPrefix')->value('value') ?? 'STU';
            $startSequence = (int) (\App\Models\SystemSetting::where('key', 'studentIdSequenceStart')->value('value') ?? 20000);
        } elseif ($request->role === 'teacher') {
            $prefix = 'TCH';
            $startSequence = 1;
        } elseif ($request->role === 'parent') {
            $prefix = 'PAR';
            $startSequence = 1;
        }

        $username = $this->generateCustomId($request->role, $prefix, $startSequence);

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
                    $hasCriteria = false;
                    if ($request->parent_phone) {
                        $q->orWhere('phone', $request->parent_phone);
                        $hasCriteria = true;
                    }
                    if ($request->parent_email) {
                        $q->orWhere('email', $request->parent_email);
                        $hasCriteria = true;
                    }
                    // If no phone/email provided, force query to fail matching (so we create a new one)
                    // unless we allow matching by name? No, name is not unique.
                    if (!$hasCriteria) {
                        $q->whereRaw('1 = 0');
                    }
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
        \Illuminate\Support\Facades\Log::info('---------------- LOGIN ATTEMPT ----------------');
        \Illuminate\Support\Facades\Log::info('Login Input:', $request->all());

        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'email' => 'required', // Can be email or username
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('Login Validation Failed', $validator->errors()->toArray());
            return response()->json(['message' => 'Please enter email and password'], 422);
        }

        // 2. Determine if logging in via Email or Username
        $loginType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        \Illuminate\Support\Facades\Log::info("Login Type: $loginType");

        // 3. Find User
        $user = User::where($loginType, $request->email)->first();

        // 4. Check Password
        if (!$user) {
            \Illuminate\Support\Facades\Log::error("User NOT FOUND for $loginType: " . $request->email);
            return response()->json(['message' => 'Invalid credentials provided.'], 401);
        }

        // --- MAINTENANCE MODE CHECK ---
        $maintenanceSetting = \App\Models\SystemSetting::where('key', 'maintenanceMode')->first();
        $isMaintenanceMode = $maintenanceSetting && ($maintenanceSetting->value === 'true' || $maintenanceSetting->value === '1' || $maintenanceSetting->value === 1 || $maintenanceSetting->value === true);

        if ($isMaintenanceMode && $user->role !== 'admin') {
            return response()->json([
                'message' => 'System is currently under maintenance. Please try again later.',
                'maintenance_mode' => true
            ], 503);
        }
        // ------------------------------

        \Illuminate\Support\Facades\Log::info('User found:', ['id' => $user->id, 'email' => $user->email, 'username' => $user->username]);

        if (!Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\Log::error('Password HASH CHECK FAILED for user ' . $user->id);
            return response()->json(['message' => 'Invalid credentials provided.'], 401);
        }

        \Illuminate\Support\Facades\Log::info('Password check PASSED. Generating token.');

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
        // --- MAINTENANCE MODE CHECK ---
        $maintenanceSetting = \App\Models\SystemSetting::where('key', 'maintenanceMode')->first();
        if ($maintenanceSetting && ($maintenanceSetting->value === 'true' || $maintenanceSetting->value === '1' || $maintenanceSetting->value === 1 || $maintenanceSetting->value === true)) {
             return response()->json([
                 'message' => 'System is currently under maintenance. Please try again later.',
                 'maintenance_mode' => true
             ], 503);
        }
        // ------------------------------

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

        // Check matches (Sanitize inputs for comparison)
        $phoneMatch = false;
        $cleanInputPhone = preg_replace('/[^0-9]/', '', $request->phone);
        $matchedSourcePhone = $request->phone; // To be used for fetching/creating user
        $foundParentUser = null;

        // 1. Check stored parent_phone on student record
        $cleanStudentContextPhone = preg_replace('/[^0-9]/', '', $student->parent_phone ?? '');

        if ($cleanStudentContextPhone === $cleanInputPhone) {
            $phoneMatch = true;
            // Prefer the DB version if it exists
            if (!empty($student->parent_phone)) {
                $matchedSourcePhone = $student->parent_phone;
            }
        }

        // 2. Fallback: Check Linked Parent Account (Real-time check)
        if (!$phoneMatch && $student->parent_id) {
             $linkedParent = User::find($student->parent_id);
             if ($linkedParent) {
                $cleanLinkedPhone = preg_replace('/[^0-9]/', '', $linkedParent->phone);
                if ($cleanLinkedPhone === $cleanInputPhone) {
                    $phoneMatch = true;
                    $foundParentUser = $linkedParent;
                    $matchedSourcePhone = $linkedParent->phone;
                }
             }
        }

        if (!$phoneMatch) {
             return response()->json(['message' => 'Phone number does not match registered parent'], 401);
        }

        // If we found the specific parent account explicitly linked, use it.
        if ($foundParentUser) {
            $parent = $foundParentUser;
        } else {
            // Otherwise, try to find a parent user with the matched source phone or the input phone
            $parent = User::where('role', 'parent')
                ->where(function($q) use ($matchedSourcePhone, $request) {
                    $q->where('phone', $matchedSourcePhone)
                      ->orWhere('phone', $request->phone);
                })->first();

            // If still not found, try to find by normalized phone (slow but necessary if inconsistent)
            if (!$parent) {
                 $allParents = User::where('role', 'parent')->get();
                 foreach($allParents as $p) {
                     if (preg_replace('/[^0-9]/', '', $p->phone) === $cleanInputPhone) {
                         $parent = $p;
                         break;
                     }
                 }
            }
        }

        if (!$parent) {
             // Create using the matched source phone format to maintain consistency
             $parent = User::create([
                 'name' => $student->parent_name ?? 'Parent',
                 'phone' => $matchedSourcePhone,
                 'role' => 'parent',
                 'email' => $matchedSourcePhone . '@parent.ems',
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

    // generateUsername method removed - using Trait generateCustomId


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
            'current_password' => $request->password ? 'required|string' : 'nullable|string'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Security Check: Verify current password if provided or required
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                 return response()->json([
                     'errors' => ['current_password' => ['Current password is incorrect']]
                 ], 422);
            }
        }

        $user->name = $request->name;
        if ($request->email) $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->plain_password = $request->password;
        }
        $user->save();
        return response()->json(['message' => 'Profile Updated Successfully', 'user' => $user]);
    }
}
