<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionListController extends Controller
{
    private function getRegions(Request $request, $folderPath, $title)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $regions = Region::whereHas('incomingFiles', function ($query) use ($startDate, $endDate, $folderPath) {
            $query->where('path', 'LIKE', "%{$folderPath}%");

            if ($startDate && $endDate) {
                $query->whereBetween('incoming_files.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } elseif ($startDate) {
                $query->whereDate('incoming_files.created_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->whereDate('incoming_files.created_at', '<=', $endDate);
            }
        })
        ->with([
            'partners' => function ($query) use ($startDate, $endDate, $folderPath) {
                $query->withCount([
                    'incomingFiles as file_count' => function ($q) use ($startDate, $endDate, $folderPath) {
                        $q->where('path', 'LIKE', "%{$folderPath}%")
                            ->leftJoin('archived_files', function ($join) {
                                $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                     ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                            })->whereNull('archived_files.id');

                        if ($startDate && $endDate) {
                            $q->whereBetween('incoming_files.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                        } elseif ($startDate) {
                            $q->whereDate('incoming_files.created_at', '>=', $startDate);
                        } elseif ($endDate) {
                            $q->whereDate('incoming_files.created_at', '<=', $endDate);
                        }
                    }
                ]);
            }
        ])
        ->withCount([
            'incomingFiles as file_count' => function ($q) use ($startDate, $endDate, $folderPath) {
                $q->where('path', 'LIKE', "%{$folderPath}%")
                    ->leftJoin('archived_files', function ($join) {
                        $join->on('incoming_files.filename', '=', 'archived_files.filename')
                             ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                    })->whereNull('archived_files.id');

                if ($startDate && $endDate) {
                    $q->whereBetween('incoming_files.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                } elseif ($startDate) {
                    $q->whereDate('incoming_files.created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $q->whereDate('incoming_files.created_at', '<=', $endDate);
                }
            }
        ])
        ->paginate(5)
        ->appends($request->only(['start_date', 'end_date']));

        return view('regions.index', [
            'regions' => $regions,
            'title' => $title,
            'label' => "Daftar Wilayah & Mitra {$title}"
        ]);
    }

    public function index(Request $request) // BRK
    {
        return $this->getRegions($request, 'BRK', 'BRK');
    }

    public function bengkuluList(Request $request)
    {
        return $this->getRegions($request, 'bengkulu', 'Bengkulu');
    }

    public function sumutList(Request $request)
    {
        return $this->getRegions($request, 'sumut', 'Sumut');
    }
}
