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
                ['name' => 'Grade 01'],
                ['name' => 'Grade 02'],
                ['name' => 'Grade 03'],
                ['name' => 'Grade 04'],
                ['name' => 'Grade 05'],
                ['name' => 'Grade 06'],
                ['name' => 'Grade 07'],
                ['name' => 'Grade 08'],
                ['name' => 'Grade 09'],
                ['name' => 'Grade 10'],
                ['name' => 'Grade 11'],
                ['name' => 'O/L'],
                ['name' => 'Grade 12'],
                ['name' => 'Grade 13'],
                ['name' => 'A/L'],
                ['name' => 'Others'],
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
