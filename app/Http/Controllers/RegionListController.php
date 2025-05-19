<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\IncomingFile;

class RegionListController extends Controller
{
    public function index(Request $request)
    {
        $regions = Region::with(['partners' => function ($query) {
            $query->withCount(['incomingFiles as file_count' => function ($q) {
                $q->leftJoin('archived_files', function ($join) {
                    $join->on('incoming_files.filename', '=', 'archived_files.filename')
                         ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
                })
                ->whereNull('archived_files.id');
            }]);
        }])->withCount(['incomingFiles as file_count' => function ($q) {
            $q->leftJoin('archived_files', function ($join) {
                $join->on('incoming_files.filename', '=', 'archived_files.filename')
                     ->on('incoming_files.partner_id', '=', 'archived_files.partner_id');
            })
            ->whereNull('archived_files.id');
        }])->paginate(5);

        return view('regions.index', compact('regions'));
    }

}
