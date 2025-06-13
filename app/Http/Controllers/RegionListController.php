<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;

class RegionListController extends Controller
{
    public function index()
    {
        // Wilayah default (BRK)
        $regions = Region::with([
            'partners' => function ($query) {
                $query->withCount([
                    'incomingFiles as file_count' => function ($q) {
                        $q->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                    }
                ]);
            }
        ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->leftJoin('archived_files', function ($join) {
                        $join->on('incoming_files.filename', '=', 'archived_files.filename')
                            ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                    })->whereNull('archived_files.id');
                }
            ])
            ->paginate(5);

        return view('regions.index', [
            'regions' => $regions,
            'title' => 'BRK'
        ]);
    }

    public function bengkuluList()
    {
        // Filter hanya wilayah bernama "Bengkulu"
        $regions = Region::with([
            'partners' => function ($query) {
                $query->withCount([
                    'incomingFiles as file_count' => function ($q) {
                        $q->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                    }
                ]);
            }
        ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->leftJoin('archived_files', function ($join) {
                        $join->on('incoming_files.filename', '=', 'archived_files.filename')
                            ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                    })->whereNull('archived_files.id');
                }
            ])
            ->whereRaw('LOWER(name) = ?', ['bengkulu'])
            ->paginate(5);

        return view('regions.bengkulu', [
            'regions' => $regions,
            'title' => 'Bengkulu'
        ]);
    }

    public function sumutList()
    {
        // Filter hanya wilayah bernama "Sumut"
        $regions = Region::with([
            'partners' => function ($query) {
                $query->withCount([
                    'incomingFiles as file_count' => function ($q) {
                        $q->leftJoin('archived_files', function ($join) {
                            $join->on('incoming_files.filename', '=', 'archived_files.filename')
                                ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                        })->whereNull('archived_files.id');
                    }
                ]);
            }
        ])
            ->withCount([
                'incomingFiles as file_count' => function ($q) {
                    $q->leftJoin('archived_files', function ($join) {
                        $join->on('incoming_files.filename', '=', 'archived_files.filename')
                            ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                    })->whereNull('archived_files.id');
                }
            ])
            ->whereRaw('LOWER(name) = ?', ['sumut'])
            ->paginate(5);

        return view('regions.sumut', [
            'regions' => $regions,
            'title' => 'Sumut'
        ]);
    }
}
