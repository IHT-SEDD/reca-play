<?php

namespace App\Console\Commands;

use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckRecordedVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordings:check-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check recordings and update status to done if two recorded videos exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $recordings = Recording::where('status', 'processing')
            ->where('end_time', '<=', $now)
            ->get();

        if ($recordings->isEmpty()) {
            $this->info('No recordings to process.');
            return;
        }

        foreach ($recordings as $recording) {
            $videoCount = RecordedVideo::where('recording_id', $recording->id)->count();

            if ($videoCount >= 2) {
                $recording->update([
                    'status' => 'done',
                    'updated_at' => now()
                ]);
                $this->info("Recording ID {$recording->id} updated to DONE (found {$videoCount} videos).");
            } else {
                $this->info("Recording ID {$recording->id} does not have 2 videos yet. ({$videoCount})");
            }
        }

        $this->info('Recording check complete.');
    }
}
