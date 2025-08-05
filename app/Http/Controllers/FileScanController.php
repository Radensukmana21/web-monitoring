<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FileScanController extends Controller
{
     public function runPythonScanner()
    {
        $scriptPath = base_path('scanner/main.py');

        // Gunakan Symfony Process untuk hasil yang lebih aman dan fleksibel
        $process = new Process(['python3', $scriptPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menjalankan Python script',
                'error' => $process->getErrorOutput(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'output' => explode("\n", $process->getOutput()),
        ]);
    }
}
