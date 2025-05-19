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

    // Ambil semua region yang punya file masuk di hari ini
    $regionsWithIncoming = Region::whereHas('incomingFiles', function ($query) use ($today) {
        $query->whereDate('detected_at', $today);
    })
    ->with(['incomingFiles' => function ($query) use ($today) {
        $query->whereDate('detected_at', $today);
    }])
    ->paginate(5);

    // Data untuk Chart per region, hanya untuk hari ini
    $chartData = Region::withCount(['incomingFiles as total_today' => function ($query) use ($today) {
        $query->whereDate('detected_at', $today);
    }])->get();

    // Data untuk kalender: jumlah file masuk per hari
    $calendarData = IncomingFile::select(
        DB::raw('DATE(detected_at) as date'),
        DB::raw('count(*) as count')
    )
    ->groupBy('date')
    ->get();

    return view('index', compact('regionsWithIncoming', 'chartData', 'calendarData'));

}

}
