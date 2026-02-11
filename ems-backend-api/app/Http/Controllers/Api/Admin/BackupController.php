<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\SystemSetting;

class BackupController extends Controller
{
    public function run(Request $request)
    {
        try {
            // Run backup command without email (to avoid SSL errors)
            Artisan::call('backup:database');

            return response()->json([
                'message' => 'Backup started successfully',
                'email_notification' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start backup: ' . $e->getMessage()
            ], 500);
        }
    }
}
