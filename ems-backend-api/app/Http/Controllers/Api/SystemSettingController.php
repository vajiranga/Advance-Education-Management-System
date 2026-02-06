<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    // Admin: Get All Settings
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    // Admin: Update Settings (Bulk)
    public function update(Request $request)
    {
        $data = $request->except(['_token']); // Get all inputs

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value] // JSON/Boolean will be cast to string automatically, front-end should handle types
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    // Public: Get Public Configs (For Login/Register pages)
    public function publicSettings()
    {
        // Define which keys are public
        $publicKeys = [
            'blockTeacherRegistration',
            'maintenanceMode',
            'allowParentLogin',
            'appName',
            'supportPhone'
        ];

        $settings = SystemSetting::whereIn('key', $publicKeys)->pluck('value', 'key');
        return response()->json($settings);
    }
}
