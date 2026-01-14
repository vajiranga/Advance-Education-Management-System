<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::all();
        
        if ($batches->isEmpty()) {
            $defaults = [
                ['name' => 'Grade 6'],
                ['name' => 'Grade 7'],
                ['name' => 'Grade 8'],
                ['name' => 'Grade 9'],
                ['name' => 'Grade 10'],
                ['name' => 'Grade 11'],
                ['name' => '2025 A/L'],
                ['name' => '2026 A/L'],
            ];
            // Mass assignment if guarded=[] or name in fillable
            foreach ($defaults as $data) {
                Batch::forceCreate($data); // forceCreate to bypass mass assignment if needed, or create if fillable
            }
            $batches = Batch::all();
        }
        
        return response()->json($batches);
    }
}
