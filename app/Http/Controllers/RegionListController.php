<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionListController extends Controller
{
    public function index()
    {
        // Wilayah default (BRK)
        $regions = Region::whereHas('incomingFiles', function ($query) {
            $query->where('path', 'LIKE', '%BRK%');
        })
            ->with([
                'partners' => function ($query) {
                    $query->withCount([
                        'incomingFiles as file_count' => function ($q) {
                            $q->where('path', 'LIKE', '%BRK%')
                                ->leftJoin('archived_files', function ($join) {
                                    $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                        ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                                })->whereNull('archived_files.id');
                        }
                    ]);
                }
            ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->where('path', 'LIKE', '%BRK%')
                        ->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                }
            ])
            ->paginate(5);

        return view('regions.index', [
            'regions' => $regions,
            'title' => 'BRK',
            'label' => 'Daftar Wilayah & Mitra BRK'
        ]);
    }

    public function bengkuluList()
    {
        $regions = Region::whereHas('incomingFiles', function ($query) {
            $query->where('path', 'LIKE', '%bengkulu%'); // otomatis berdasar folder path
        })
            ->with([
                'partners' => function ($query) {
                    $query->withCount([
                        'incomingFiles as file_count' => function ($q) {
                            $q->where('path', 'LIKE', '%bengkulu%')
                                ->leftJoin('archived_files', function ($join) {
                                    $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                        ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                                })->whereNull('archived_files.id');
                        }
                    ]);
                }
            ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->where('path', 'LIKE', '%bengkulu%')
                        ->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                }
            ])
            ->paginate(5);

        return view('regions.index', [
            'regions' => $regions,
            'title' => 'Bengkulu',
            'label' => 'Daftar Wilayah & Mitra Bengkulu'
        ]);
    }


    public function sumutList()
    {
        $regions = Region::whereHas('incomingFiles', function ($query) {
            $query->where('path', 'LIKE', '%sumut%');
        })
            ->with([
                'partners' => function ($query) {
                    $query->withCount([
                        'incomingFiles as file_count' => function ($q) {
                            $q->where('path', 'LIKE', '%sumut%')
                                ->leftJoin('archived_files', function ($join) {
                                    $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                        ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                                })->whereNull('archived_files.id');
                        }
                    ]);
                }
            ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->where('path', 'LIKE', '%sumut%')
                        ->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                }
            ])
            ->paginate(5);

        return view('regions.index', [
            'regions' => $regions,
            'title' => 'Sumut',
            'label' => 'Daftar Wilayah & Mitra Sumut'
        ]);
    }
}
