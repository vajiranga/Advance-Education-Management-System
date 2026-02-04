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

        $perPage = $request->input('per_page', 20);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // If fetching parents, attach their linked children (Student IDs)
        if ($role === 'parent') {
            $users->getCollection()->transform(function ($parent) {
                $childrenQuery = User::where('role', 'student')
                    ->where(function ($q) use ($parent) {
                        if (!empty($parent->email)) $q->where('parent_email', $parent->email);
                        if (!empty($parent->phone)) $q->orWhere('parent_phone', $parent->phone);
                        // Also try to match cleaned phone numbers if raw match fails
                        if (!empty($parent->phone)) {
                            $cleanParentPhone = preg_replace('/[^0-9]/', '', $parent->phone);
                            if ($cleanParentPhone) $q->orWhere('parent_phone', 'like', "%{$cleanParentPhone}%");
                        }
                        $q->orWhere('parent_id', $parent->id);
                    });

                // Fetch linked students
                $children = $childrenQuery->pluck('username'); // Get STU numbers

                $parent->student_ids = $children; // Attach to object
                return $parent;
            });
        }

        return response()->json($users);
    }


    public function store(Request $request)
    {
        // Reusing similar validation logic but as admin
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'required|in:student,teacher,parent',
            'email' => $request->role === 'teacher' ? 'required|string|email|max:255|unique:users' : 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',

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

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Sanitize Phone Numbers (Remove non-numeric characters)
            $phone = $request->phone ? preg_replace('/[^0-9]/', '', $request->phone) : null;
            $whatsapp = $request->whatsapp ? preg_replace('/[^0-9]/', '', $request->whatsapp) : null;
            $parentPhone = $request->parent_phone ? preg_replace('/[^0-9]/', '', $request->parent_phone) : null;

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
                'phone' => $phone,
                'whatsapp' => $whatsapp,
                'school' => $request->school,
                'grade' => $request->grade,

                'parent_name' => $request->parent_name,
                'parent_phone' => $parentPhone, // Use sanitized
                'parent_email' => $request->parent_email,

                'nic' => $request->nic,
                'qualifications' => $request->qualifications,
                'subjects' => $request->subjects,
                'experience' => $request->experience,
            ]);

            // Auto-Create or Link Parent Account for Students
            if ($request->role === 'student' && ($request->parent_email || $parentPhone)) {

                // Search for ANY existing user with this email or phone (Role doesn't matter strictly, to avoid Dupes)
                $parentUser = User::where(function($q) use ($request, $parentPhone) {
                        if ($request->parent_email) $q->where('email', $request->parent_email);
                        if ($parentPhone) $q->orWhere('phone', $parentPhone);
                    })->first();

                if (!$parentUser) {
                    // Start Parent Username
                    $parentPrefix = 'PAR';
                    do {
                        $parentUsername = $parentPrefix . date('Y') . rand(1000, 9999);
                    } while (User::where('username', $parentUsername)->exists());

                    // Create New Parent
                    $parentUser = User::create([
                        'name' => $request->parent_name ?? 'Parent of ' . $request->name,
                        'email' => $request->parent_email,
                        'phone' => $parentPhone, // Stored in main phone column
                        'username' => $parentUsername,
                        'password' => \Illuminate\Support\Facades\Hash::make('ems12345'), // Default Password
                        'plain_password' => 'ems12345',
                        'role' => 'parent'
                    ]);
                }

                // Link Parent ID to Student
                $user->parent_id = $parentUser->id;
                $user->parent_phone = $parentPhone;
                $user->save();
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('User Creation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
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

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $user->fill($request->except(['password', 'username', 'role']));

            // Sanitize phones on update
            if ($request->has('phone')) $user->phone = preg_replace('/[^0-9]/', '', $request->phone);
            if ($request->has('whatsapp')) $user->whatsapp = preg_replace('/[^0-9]/', '', $request->whatsapp);
            if ($request->has('parent_phone')) $user->parent_phone = preg_replace('/[^0-9]/', '', $request->parent_phone);

            if ($request->filled('password')) {
                $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
                $user->plain_password = $request->password;
            }

            // --- PARENT SYNC & AUTO-LINK LOGIC ---
            if ($user->role === 'student') {

                // 1. If NO parent linked yet, try to find or create one now
                if (!$user->parent_id && ($request->parent_email || $user->parent_phone)) {
                    $parentUser = User::where('role', 'parent')
                        ->where(function($q) use ($request, $user) {
                            if ($request->parent_email) $q->where('email', $request->parent_email);
                            if ($user->parent_phone) $q->orWhere('phone', $user->parent_phone);
                        })->first();

                    if (!$parentUser) {
                        // Create New Parent if not found
                        $parentPrefix = 'PAR';
                        do {
                            $parentUsername = $parentPrefix . date('Y') . rand(1000, 9999);
                        } while (User::where('username', $parentUsername)->exists());

                        $parentUser = User::create([
                            'name' => $request->parent_name ?? 'Parent of ' . $request->name,
                            'email' => $request->parent_email,
                            'phone' => $user->parent_phone,
                            'username' => $parentUsername,
                            'password' => \Illuminate\Support\Facades\Hash::make('ems12345'),
                            'plain_password' => 'ems12345',
                            'role' => 'parent'
                        ]);
                    }

                    // Link the found/created parent
                    $user->parent_id = $parentUser->id;
                }

                // 2. If parent IS linked (or just linked above), sync details
                if ($user->parent_id) {
                    $parentUser = User::find($user->parent_id);
                    if ($parentUser) {
                        $dataChanged = false;

                        // Sync Name
                        if ($request->filled('parent_name') && $parentUser->name !== $request->parent_name) {
                            $parentUser->name = $request->parent_name;
                            $dataChanged = true;
                        }

                        // Sync Phone (Sanitized)
                        if ($user->parent_phone && $parentUser->phone !== $user->parent_phone) {
                            $parentUser->phone = $user->parent_phone;
                            $dataChanged = true;
                        }

                        // Sync Email
                        if ($request->filled('parent_email') && $request->parent_email !== $parentUser->email) {
                            // Check uniqueness ignoring self
                            $exists = User::where('email', $request->parent_email)->where('id', '!=', $parentUser->id)->exists();
                            if (!$exists) {
                                $parentUser->email = $request->parent_email;
                                $dataChanged = true;
                            }
                        }

                        if ($dataChanged) {
                            $parentUser->save();
                        }
                    }
                }
            }

            // --- PARENT UPDATED -> SYNC CHILDREN ---
            if ($user->role === 'parent') {
                $children = User::where('parent_id', $user->id)->get();
                foreach ($children as $child) {
                    $child->parent_name = $user->name;
                    $child->parent_phone = $user->phone;
                    // Only update email if it was previously matching or we want strict sync.
                    // Usually parent_email column in student table is just a reference, so sync it.
                    $child->parent_email = $user->email;
                    $child->save();
                }
            }

            $user->save();

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['message' => 'User updated successfully', 'user' => $user]);

        } catch (\Exception $e) {
             \Illuminate\Support\Facades\DB::rollBack();
             \Illuminate\Support\Facades\Log::error('User Update Error: ' . $e->getMessage());
             return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
