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
        foreach ($this->basePaths as $basePath) {
            if (!File::exists($basePath)) {
                $this->warn("Tidak bisa mengakses: $basePath");
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

                    // Scan folder New
                    if (File::exists($newPath)) {
                        $files = File::files($newPath);

                        foreach ($files as $file) {
                            $this->info("Memproses file di New: {$file->getFilename()}");

                            $exists = IncomingFile::where('filename', $file->getFilename())
                                ->where('region_id', $region->id)
                                ->where('partner_id', $partner->id)
                                ->exists();

                            if (!$exists) {
                                IncomingFile::create([
                                    'filename' => $file->getFilename(),
                                    'path' => $file->getRealPath(),
                                    'region_id' => $region->id,
                                    'partner_id' => $partner->id,
                                    'detected_at' => Carbon::createFromTimestamp($file->getMTime()),
                                ]);

                                $this->info("📥 File baru: {$file->getFilename()} ($regionName/$partnerName)");
                            } else {
                                $this->info("File sudah ada di DB, skip: {$file->getFilename()}");
                            }
                        }
                    }

                    // Scan folder Proceed
                    if (File::exists($proceedPath)) {
                        $proceedFiles = File::files($proceedPath);

                        foreach ($proceedFiles as $pFile) {
                            $this->info("Memproses file di Proceed: {$pFile->getFilename()}");

                            $archived = ArchivedFile::where('filename', $pFile->getFilename())
                                ->where('region_id', $region->id)
                                ->where('partner_id', $partner->id)
                                ->exists();

                            if (!$archived) {
                                ArchivedFile::create([
                                    'filename' => $pFile->getFilename(),
                                    'moved_at' => Carbon::createFromTimestamp($pFile->getMTime()),
                                    'region_id' => $region->id,
                                    'partner_id' => $partner->id,
                                ]);

                                IncomingFile::where('filename', $pFile->getFilename())
                                    ->where('region_id', $region->id)
                                    ->where('partner_id', $partner->id)
                                    ->delete();

                                $this->info("📤 File diarsipkan: {$pFile->getFilename()} ($regionName/$partnerName)");
                            } else {
                                $this->info("File sudah diarsipkan, skip: {$pFile->getFilename()}");
                            }
                        }
                    }
                }
            }
        }
    }
}