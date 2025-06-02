<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ScanController extends Controller
{
    public function run(Request $request)
    {
        Artisan::call('scan:localdisk');
        return response()->json([
            'message' => 'Scan berhasil dijalankan',
            'output' => Artisan::output(),
        ]);
    }

}
