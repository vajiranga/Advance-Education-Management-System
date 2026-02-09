<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Upload Institute Logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048' // 2MB max
        ]);

        try {
            // Delete old logo if exists
            $oldLogo = SystemSetting::where('key', 'logoUrl')->first();
            if ($oldLogo && $oldLogo->value) {
                $oldPath = str_replace(url('/'), '', $oldLogo->value);
                if (file_exists(public_path($oldPath))) {
                    unlink(public_path($oldPath));
                }
            }

            // Upload new logo
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/logo'), $filename);
            // innovative: Store relative path to avoid domain/port mismatch
            $logoUrl = '/uploads/logo/' . $filename;

            // Save to database
            SystemSetting::updateOrCreate(
                ['key' => 'logoUrl'],
                ['value' => $logoUrl]
            );

            return response()->json([
                'message' => 'Logo uploaded successfully',
                'logoUrl' => $logoUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove Institute Logo
     */
    public function removeLogo()
    {
        try {
            $logo = SystemSetting::where('key', 'logoUrl')->first();

            if ($logo && $logo->value) {
                // Delete file
                $oldPath = str_replace(url('/'), '', $logo->value);
                if (file_exists(public_path($oldPath))) {
                    unlink(public_path($oldPath));
                }

                // Remove from database
                $logo->delete();
            }

            return response()->json([
                'message' => 'Logo removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
