<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ParentController extends Controller
{
    public function getChildren(Request $request)
    {
        $parent = $request->user();
        
        // Find students where parent_phone matches the logged-in parent's phone
        // Also check parent_email just in case? No, phone is primary identifier for parent login.
        
        $children = User::where('role', 'student')
                        ->where('parent_phone', $parent->phone)
                        ->get();

        return response()->json($children);
    }
}
