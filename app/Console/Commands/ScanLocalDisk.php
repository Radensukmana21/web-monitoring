<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\IncomingFile;
use App\Models\Partner;
use App\Models\Region;
use Carbon\Carbon;

class ScanLocalDisk extends Command
{
    protected $signature = 'scan:localdisk';
    protected $description = 'Scan folder "New" untuk file tahun berjalan dari semua path';

    public function handle(): void
    {
        $basePaths = config('filescan.base_paths');
        $scanned = 0;
        $skipped = 0;
        $currentYear = now()->year;

        foreach ($basePaths as $basePath) {
            if (!File::exists($basePath)) {
                $this->warn("❌ Tidak bisa mengakses: $basePath");
                continue;
            }

            $regionFolders = File::directories($basePath);

            foreach ($regionFolders as $regionPath) {
                $regionName = basename($regionPath);
                $region = Region::firstOrCreate(['name' => $regionName]);

                $partnerFolders = File::directories($regionPath);

                foreach ($partnerFolders as $partnerPath) {
                    $partnerName = basename($partnerPath);
                    $partner = Partner::firstOrCreate([
                        'region_id' => $region->id,
                        'name' => $partnerName,
                    ]);

                    $newPath = $partnerPath . DIRECTORY_SEPARATOR . 'New';
                    if (!File::exists($newPath)) continue;

                    foreach (File::files($newPath) as $file) {
                        $fileYear = Carbon::createFromTimestamp($file->getMTime())->year;
                        if ($fileYear !== $currentYear) continue;

                        $filename = $file->getFilename();
                        $exists = IncomingFile::where('filename', $filename)
                            ->where('region_id', $region->id)
                            ->where('partner_id', $partner->id)
                            ->exists();

                        if (!$exists) {
                            IncomingFile::create([
                                'filename' => $filename,
                                'path' => $file->getRealPath(),
                                'region_id' => $region->id,
                                'partner_id' => $partner->id,
                                'detected_at' => Carbon::createFromTimestamp($file->getMTime()),
                            ]);
                            $this->info("📥 Baru: $filename ($regionName/$partnerName)");
                            $scanned++;
                        } else {
                            $this->line("⏭️  Skip: $filename ($regionName/$partnerName)");
                            $skipped++;
                        }
                    }
                }
            }
        }

        $this->newLine();
        $this->info("✅ Scan selesai.");
        $this->line("➕ Ditambahkan : $scanned");
        $this->line("⏭️  Di-skip     : $skipped");
    }
}
