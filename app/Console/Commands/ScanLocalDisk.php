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
    protected $description = 'Scan folder "New" dari struktur direktori flat maupun nested';

    public function handle(): void
    {
        $basePaths = config('filescan.base_paths');
        $scanned = 0;
        $skipped = 0;
        $currentYear = now()->year;

        foreach ($basePaths as $basePath) {
            $this->info("🔍 Base path: $basePath");

            if (!File::exists($basePath)) {
                $this->warn("❌ Tidak bisa mengakses: $basePath");
                continue;
            }

            $isFlatStructure = $this->pathIsFlat($basePath);
            $firstFolders = File::directories($basePath);

            if ($isFlatStructure) {
                // Struktur flat: langsung partner ➝ New
                $regionName = basename($basePath);
                $region = Region::firstOrCreate(['name' => $regionName]);

                foreach ($firstFolders as $partnerPath) {
                    $partnerName = basename($partnerPath);
                    $partner = Partner::firstOrCreate([
                        'region_id' => $region->id,
                        'name' => $partnerName,
                    ]);

                    $newPath = $this->getNewFolderCaseInsensitive($partnerPath);
                    if (!$newPath) {
                        $this->warn("❌ Folder 'New' tidak ditemukan di: $partnerPath");
                        continue;
                    }

                    $this->scanFiles($newPath, $region, $partner, $currentYear, $scanned, $skipped);
                }

            } else {
                // Struktur nested: region ➝ partner ➝ New
                foreach ($firstFolders as $regionPath) {
                    $regionName = basename($regionPath);
                    $region = Region::firstOrCreate(['name' => $regionName]);

                    $partnerFolders = File::directories($regionPath);
                    foreach ($partnerFolders as $partnerPath) {
                        $partnerName = basename($partnerPath);
                        $partner = Partner::firstOrCreate([
                            'region_id' => $region->id,
                            'name' => $partnerName,
                        ]);

                        $newPath = $this->getNewFolderCaseInsensitive($partnerPath);
                        if (!$newPath) {
                            $this->warn("❌ Folder 'New' tidak ditemukan di: $partnerPath");
                            continue;
                        }

                        $this->scanFiles($newPath, $region, $partner, $currentYear, $scanned, $skipped);
                    }
                }
            }
        }

        $this->newLine();
        $this->info("✅ Scan selesai.");
        $this->line("➕ Ditambahkan : $scanned");
        $this->line("⏭️  Di-skip     : $skipped");
    }

    protected function getNewFolderCaseInsensitive(string $parentPath): ?string
    {
        if (!File::exists($parentPath)) return null;

        $dirs = File::directories($parentPath);
        foreach ($dirs as $dir) {
            if (strtolower(basename($dir)) === 'new') {
                return $dir;
            }
        }
        return null;
    }

    protected function pathIsFlat(string $basePath): bool
    {
        // Tambahkan nama base folder yang struktur direktori-nya flat (tanpa region)
        $flatFolders = ['bengkulu']; 
        return in_array(strtolower(basename($basePath)), $flatFolders);
    }

    protected function scanFiles(string $newPath, $region, $partner, int $currentYear, int &$scanned, int &$skipped): void
    {
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
                $this->info("📥 Baru: $filename ({$region->name}/{$partner->name})");
                $scanned++;
            } else {
                $this->line("⏭️  Skip: $filename ({$region->name}/{$partner->name})");
                $skipped++;
            }
        }
    }
}
