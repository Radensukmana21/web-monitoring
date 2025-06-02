<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\IncomingFile;
use Carbon\Carbon;

use Illuminate\Support\Facades\File;
use App\Models\ArchivedFile;
use App\Models\Partner;

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

    // protected array $basePaths = [
    //     '\\\\10.20.10.98\\backup\\BRK', // Folder utama
    // ];

    // public function testScan(){
    //     $scanned = 0;
    //     $skipped = 0;
    //     $archived = 0;
    //     $message = '';
    //     // return response()->json([
    //     //         'status' => 'success',
    //     //         'message' => $message
    //     //     ]);

    //     try {
    //         foreach ($this->basePaths as $basePath) {
    //             if (!File::exists($basePath)) {
    //                 // $this->warn("❌ Tidak bisa mengakses: $basePath");
    //                 return back()->with('error',"❌ Tidak bisa mengakses: $basePath");
    //                 continue;
    //             }

    //             $today = Carbon::today();
    //             $regionFolders = File::directories($basePath);
    
    //             $todayFolders = collect($regionFolders)->filter(function ($folderPath) use ($today) {
    //                 $lastModified = Carbon::createFromTimestamp(File::lastModified($folderPath));
    //                 return $lastModified->isSameDay($today);
    //             });
    //             // $regionFolders = File::directories($this->basePaths[0]);
    
    //             foreach ($todayFolders as $regionPath) {
                    
    //                 $regionName = basename($regionPath);
    //                 $region = Region::firstOrCreate(['name' => $regionName]);
    
    //                 $partnerFolders = File::directories($regionPath);
                   
    //                 foreach ($partnerFolders as $partnerPath) {
    //                     $partnerName = basename($partnerPath);
    //                     $partner = Partner::firstOrCreate([
    //                         'region_id' => $region->id,
    //                         'name' => $partnerName,
    //                     ]);
    
    //                     $newPath = $partnerPath . DIRECTORY_SEPARATOR . 'New';
    //                     $proceedPath = $partnerPath . DIRECTORY_SEPARATOR . 'Proceed';
    
    //                     // 1. SCAN folder NEW
    //                     if (File::exists($newPath)) {
    //                         $files = File::files($newPath);
    
    //                         foreach ($files as $file) {
    //                             $filename = $file->getFilename();
    
    //                             $alreadyExists = IncomingFile::where('filename', $filename)
    //                                 ->where('region_id', $region->id)
    //                                 ->where('partner_id', $partner->id)
    //                                 ->exists();
    
    //                             if (!$alreadyExists) {
    //                                 IncomingFile::create([
    //                                     'filename' => $filename,
    //                                     'path' => $file->getRealPath(),
    //                                     'region_id' => $region->id,
    //                                     'partner_id' => $partner->id,
    //                                     'detected_at' => Carbon::createFromTimestamp($file->getMTime()),
    //                                 ]);
    
    //                                 $message .= "📥 File baru: $filename ($regionName/$partnerName) <br>";
    //                                 // $this->info("📥 File baru: $filename ($regionName/$partnerName)");
    //                                 $scanned++;
    //                             } else {
    //                                 $message .= "⏭️  Skip (sudah ada): $filename ($regionName/$partnerName) <br>";
    //                                 // $this->line("⏭️  Skip (sudah ada): $filename ($regionName/$partnerName)");
    //                                 $skipped++;
    //                             }
    //                         }
    //                     }
    
    //                     // 2. SCAN folder PROCEED → Pindah ke Archived
    //                     if (File::exists($proceedPath)) {
    //                         $proceedFiles = File::files($proceedPath);
    
    //                         foreach ($proceedFiles as $pFile) {
    //                             $filename = $pFile->getFilename();
    
    //                             $alreadyArchived = ArchivedFile::where('filename', $filename)
    //                                 ->where('region_id', $region->id)
    //                                 ->where('partner_id', $partner->id)
    //                                 ->exists();
    
    //                             if (!$alreadyArchived) {
    //                                 ArchivedFile::create([
    //                                     'filename' => $filename,
    //                                     'moved_at' => Carbon::createFromTimestamp($pFile->getMTime()),
    //                                     'region_id' => $region->id,
    //                                     'partner_id' => $partner->id,
    //                                 ]);
    
    //                                 // Hapus dari Incoming jika masih ada
    //                                 IncomingFile::where('filename', $filename)
    //                                     ->where('region_id', $region->id)
    //                                     ->where('partner_id', $partner->id)
    //                                     ->delete();
    
    //                                 $message .= "📤 File diarsipkan: $filename ($regionName/$partnerName) <br>";
    //                                 // $this->info("📤 File diarsipkan: $filename ($regionName/$partnerName)");
    //                                 $archived++;
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }


    //         return response()->json([
    //             'status' => 'success',
    //             'message' => $message
    //         ]);
    //     } catch (\Exception $e) {
    //          return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage().' - '.$e->getLine()
    //         ]);
    //     }

    // }

}
