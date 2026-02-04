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

        // Auto-seed removed to use Seeder data

        // $batches = Batch::all(); // Moved down


        return response()->json($batches);
    }
}
