<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteOldTempRecording extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp-recordings:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete temp recordings older than 3 to 5 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('temp_recordings');

        $files = File::files($path);

        $now = now();

        foreach ($files as $file) {
            $created = \Carbon\Carbon::createFromTimestamp(File::lastModified($file));
            $ageInDays = $created->diffInDays($now);

            if ($ageInDays >= 3 && $ageInDays <= 5) {
                File::delete($file);
                $this->info("Deleted: " . $file->getFilename());
            }
        }

        $this->info('Old temp recordings cleaned successfully.');
        return 0;
    }
}
