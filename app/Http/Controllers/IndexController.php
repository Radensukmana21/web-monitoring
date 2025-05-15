<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\IncomingFile;
use Carbon\Carbon;

class IndexController extends Controller
{
     public function index()
    {
        // Ambil semua region yang memiliki file masuk hari ini
        // $today = Carbon::today();

        // $regionsWithIncoming = Region::whereHas('incomingFiles', function ($query) use ($today) {
        //     $query->whereDate('detected_at', $today);
        // })->with(['incomingFiles' => function ($query) use ($today) {
        //     $query->whereDate('detected_at', $today);
        // }])->get();
        $today = Carbon::today();
        $regionsWithIncoming = Region::with('incomingFiles')->get();

        // Data untuk Chart: jumlah file masuk per wilayah
        $chartData = Region::withCount(['incomingFiles as total_today' => function ($query) use ($today) {
            $query->whereDate('detected_at', $today);
        }])->get();

        // Data untuk Kalender: jumlah file per tanggal
        $calendarData = IncomingFile::select(
            DB::raw('DATE(detected_at) as date'),
            DB::raw('count(*) as count')
        )
        ->groupBy('date')
        ->get();

        return view('index', compact('regionsWithIncoming', 'chartData', 'calendarData'));
    }
}
