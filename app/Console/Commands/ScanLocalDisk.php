<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\IncomingFile;
use App\Models\ArchivedFile;
use App\Models\Partner;
use App\Models\Region;
use Carbon\Carbon;

class ScanLocalDisk extends Command
{
    protected $signature = 'scan:localdisk';
    protected $description = 'Scan folder jaringan untuk file baru dan pindahan ke arsip';

    protected array $basePaths = [
        '\\\\10.20.10.98\\backup\\BRK', // Folder utama
    ];

    public function handle(): void
    {
        $scanned = 0;
        $skipped = 0;
        $archived = 0;

        foreach ($this->basePaths as $basePath) {
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
                    $proceedPath = $partnerPath . DIRECTORY_SEPARATOR . 'Proceed';

                    // 1. SCAN folder NEW
                    if (File::exists($newPath)) {
                        $files = File::files($newPath);

                        foreach ($files as $file) {
                            $filename = $file->getFilename();

                            $alreadyExists = IncomingFile::where('filename', $filename)
                                ->where('region_id', $region->id)
                                ->where('partner_id', $partner->id)
                                ->exists();

                            if (!$alreadyExists) {
                                IncomingFile::create([
                                    'filename' => $filename,
                                    'path' => $file->getRealPath(),
                                    'region_id' => $region->id,
                                    'partner_id' => $partner->id,
                                    'detected_at' => Carbon::createFromTimestamp($file->getMTime()),
                                ]);
                                $this->info("📥 File baru: $filename ($regionName/$partnerName)");
                                $scanned++;
                            } else {
                                $this->line("⏭️  Skip (sudah ada): $filename ($regionName/$partnerName)");
                                $skipped++;
                            }
                        }
                    }

                    // 2. SCAN folder PROCEED → Pindah ke Archived
                    if (File::exists($proceedPath)) {
                        $proceedFiles = File::files($proceedPath);

                        foreach ($proceedFiles as $pFile) {
                            $filename = $pFile->getFilename();

                            $alreadyArchived = ArchivedFile::where('filename', $filename)
                                ->where('region_id', $region->id)
                                ->where('partner_id', $partner->id)
                                ->exists();

                            if (!$alreadyArchived) {
                                ArchivedFile::create([
                                    'filename' => $filename,
                                    'moved_at' => Carbon::createFromTimestamp($pFile->getMTime()),
                                    'region_id' => $region->id,
                                    'partner_id' => $partner->id,
                                ]);

                                // Hapus dari Incoming jika masih ada
                                IncomingFile::where('filename', $filename)
                                    ->where('region_id', $region->id)
                                    ->where('partner_id', $partner->id)
                                    ->delete();

                                $this->info("📤 File diarsipkan: $filename ($regionName/$partnerName)");
                                $archived++;
                            }
                        }
                    }
                }
            }
        }
        // Summary
        $this->newLine();
        $this->info("✅ Scan selesai.");
        $this->line("➕ File baru ditambahkan : $scanned");
        $this->line("⏭️  File di-skip         : $skipped");
        $this->line("📦 File diarsipkan       : $archived");
    }
}