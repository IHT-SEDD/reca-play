<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        $tables = [
            'qr_sessions',
            'record_sessions',
            'stream_sessions',
        ];

        $cutoff = Carbon::now()->subHour();

        foreach ($tables as $table) {

            $deleted = DB::table($table)
                ->whereNull('user_id')
                ->whereNotNull('session_token')
                ->whereNotNull('qr_token')
                ->where('last_active_at', '<', $cutoff)
                ->delete();

            $this->info("Deleted {$deleted} rows from {$table}.");
        }

        return Command::SUCCESS;
    }
}
