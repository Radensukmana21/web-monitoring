<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\ArchivedFile;
use App\Models\Partner;
use App\Models\Region;
use App\Models\IncomingFile;
use Carbon\Carbon;


class IndexController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'today'); // default 'today'
        $today = Carbon::today();
        $startDate = match ($range) {
            '7days' => Carbon::now()->subDays(6), // termasuk hari ini
            '30days' => Carbon::now()->subDays(29),
            'all' => null,
            default => $today,
        };

        // Ambil semua region yang punya file masuk di range tersebut
        $regionsQuery = Region::whereHas('incomingFiles', function ($query) use ($startDate, $today, $range) {
            if ($range === 'today') {
                $query->whereDate('detected_at', $today);
            } elseif ($startDate) {
                $query->whereBetween('detected_at', [$startDate, $today]);
            }
        });

        $regionsWithIncoming = $regionsQuery->with([
            'incomingFiles' => function ($query) use ($startDate, $today, $range) {
                if ($range === 'today') {
                    $query->whereDate('detected_at', $today);
                } elseif ($startDate) {
                    $query->whereBetween('detected_at', [$startDate, $today]);
                }
            }
        ])->paginate(5);

        // Chart berdasarkan region dan path
        $charts = ['BRK' => 'chartBrk', 'bengkulu' => 'chartBengkulu', 'sumut' => 'chartSumut'];
        $chartData = [];

        foreach ($charts as $key => $varName) {
            $query = Region::withCount([
                'incomingFiles as total_today' => function ($query) use ($startDate, $today, $key, $range) {
                    if ($range === 'today') {
                        $query->whereDate('detected_at', $today);
                    } elseif ($startDate) {
                        $query->whereBetween('detected_at', [$startDate, $today]);
                    }
                    $query->where('path', 'LIKE', '%' . $key . '%');
                }
            ])->get()->filter(fn($item) => $item->total_today > 0)->values();

            $chartData[$varName] = $query;
        }

        // Data untuk kalender
        $calendarQuery = IncomingFile::select(
            DB::raw('DATE(detected_at) as date'),
            DB::raw('count(*) as count')
        );

        if ($range === 'today') {
            $calendarQuery->whereDate('detected_at', $today);
        } elseif ($startDate) {
            $calendarQuery->whereBetween('detected_at', [$startDate, $today]);
        }

        $calendarData = $calendarQuery->groupBy('date')->get();

        return view('index', [
            'regionsWithIncoming' => $regionsWithIncoming,
            'chartBrk' => $chartData['chartBrk'],
            'chartBengkulu' => $chartData['chartBengkulu'],
            'chartSumut' => $chartData['chartSumut'],
            'calendarData' => $calendarData,
            'range' => $range,
        ]);
    }
}
