<?php

namespace App\Console\Commands;

use App\Models\IncomingFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ScanLocalDisk extends Command
{
    protected $signature = 'scan:localdisk';
    protected $description = 'Scan network folders for new files';

    protected array $folderPaths = [];

    public function __construct()
    {
        parent::__construct();

        // Inisialisasi folder utama (backup dan backupftp)
        $this->folderPaths = [
            '\\\\10.20.10.98\\backup\bengkulu',     // Folder utama backup
            // '\\\\10.20.10.98\\backupftp',  // Folder utama backupftp
        ];
    }

    public function handle()
    {
        foreach ($this->folderPaths as $path) {
            $this->info("Memindai folder: $path");

            if (!File::exists($path)) {
                $this->warn("Folder tidak ditemukan atau tidak bisa diakses: $path");
                continue;
            }

            // Ambil semua subfolder dalam folder utama
            $subfolders = File::directories($path);

            foreach ($subfolders as $subfolder) {
                $this->info("Memindai subfolder: $subfolder");

                // Ambil semua file dalam subfolder
                $newFolder = $subfolder . DIRECTORY_SEPARATOR . 'NEW';

            if (File::exists($newFolder)) {
                $files = File::files($newFolder); // hanya ambil file di folder 'new', tidak termasuk subfolder

                foreach ($files as $file) {
                    $realPath = $file->getRealPath();
                    $exists = IncomingFile::where('path', $realPath)->exists();

                    if (!$exists) {
                        $fileTimestamp = Carbon::createFromTimestamp($file->getMTime()); // waktu terakhir modifikasi file

                        IncomingFile::create([
                            'filename' => $file->getFilename(),
                            'path' => $realPath,
                            'detected_at' => $fileTimestamp, // gunakan waktu modifikasi file
                        ]);

                        $this->info('File baru ditemukan: ' . $file->getFilename());
                    }
                }
            }
            }
        }

    }
}