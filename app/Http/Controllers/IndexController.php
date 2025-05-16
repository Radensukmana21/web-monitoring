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
    $today = Carbon::today();

    // Hanya ambil region yang memiliki file masuk
    $regionsWithIncoming = Region::whereHas('incomingFiles') // HANYA YANG PUNYA FILE
        ->with('incomingFiles') // Muat relasi file
        ->paginate(5);

    // Data untuk Chart
    $chartData = Region::withCount(['incomingFiles as total_today' => function ($query) use ($today) {
        $query->whereDate('detected_at', $today);
    }])->get();

    // Data untuk Kalender
    $calendarData = IncomingFile::select(
        DB::raw('DATE(detected_at) as date'),
        DB::raw('count(*) as count')
    )
    ->groupBy('date')
    ->get();

    return view('index', compact('regionsWithIncoming', 'chartData', 'calendarData'));
}

}
