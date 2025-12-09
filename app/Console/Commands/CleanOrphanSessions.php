<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanOrphanSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean-orphan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete session in qr_sessions, record_sessions, stream_sessions where user_id null and last_active lebih 1 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subHour();

        try {
            DB::beginTransaction();

            $qrSessions = DB::table('qr_sessions')->get();

            $deletedCount = 0;

            foreach ($qrSessions  as $qr) {


                $exists = DB::table('record_sessions')
                    ->where('session_token', $qr->session_token)
                    ->exists();

                if ($exists) {
                    continue;
                }

                if (Carbon::parse($qr->last_active_at)->lt($cutoff)) {
                    DB::table('qr_sessions')
                        ->where('id', $qr->id)
                        ->delete();

                    $deletedCount++;
                }
            }

            DB::commit();

            $message = "CleanOrphanSessions: Deleted {$deletedCount} orphan qr_sessions at " . now();

            $this->info($message);
            Log::channel('orphan-session')->info($message);
        } catch (\Exception $e) {

            DB::rollBack();

            $errorMsg = "CleanOrphanSessions ERROR: " . $e->getMessage();
            $this->error($errorMsg);
            Log::channel('orphan-session')->error($errorMsg);
        }

        return Command::SUCCESS;
    }
}
