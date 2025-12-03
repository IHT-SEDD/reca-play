<?php

namespace App\Console\Commands;

use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanupOldRecordings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recordings:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete videos and thumbnails that are more than 5 days old from storage and database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Start cleaning up old videos...');

        $fiveDaysAgo = Carbon::now()->subDays(5);

        $oldVideos = RecordedVideo::where('created_at', '<=', $fiveDaysAgo)->get();

        foreach ($oldVideos as $video) {
            if ($video->video_path && Storage::disk('public')->exists('recordings/' . $video->video_path)) {
                Storage::disk('public')->delete('recordings/' . $video->video_path);
                $this->info("Video deleted: " . $video->video_path);
            }

            if ($video->thumbnail_path && Storage::disk('public')->exists('thumbnails/' . $video->thumbnail_path)) {
                Storage::disk('public')->delete('thumbnails/' . $video->thumbnail_path);
                $this->info("Thumbnail deleted: " . $video->thumbnail_path);
            }

            if ($video->recording_id) {
                $deleted = Recording::where('id', $video->recording_id)->delete();
                if ($deleted) {
                    $this->info("Recording deleted from recordings table: ID " . $video->recording_id);
                }
            }

            $video->delete();
            $this->info("RecordedVideo deleted: ID " . $video->id);
        }

        $this->info('Cleaning up finished.');
    }
}
