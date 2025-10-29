<?php

namespace App\Console\Commands\Session;

use App\Models\Session\SessionCode;
use App\Enums\SessionCodeStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSessionCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-session-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark expired session codes as expired based on expired_at datetime';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $expiredCount = SessionCode::whereNotNull('expired_at')
                ->where('expired_at', '<=', now())
                ->whereNotIn('status', [
                    SessionCodeStatus::Expired,
                    SessionCodeStatus::InUse,
                    SessionCodeStatus::Done,
                ])
                ->update([
                    'status' => SessionCodeStatus::Expired,
                    'updated_at' => now(),
                ]);

            if ($expiredCount > 0) {
                $message = "✅ {$expiredCount} session code(s) marked as expired at " . now();
                $this->info($message);
                Log::channel('expired-session')->info('[ExpireSessionCodes] ' . $message);
            } else {
                $message = "ℹ️ No session codes to expire at " . now();
                $this->info($message);
                Log::channel('expired-session')->info('[ExpireSessionCodes] ' . $message);
            }
        } catch (\Throwable $e) {
            Log::channel('expired-session')->error('[ExpireSessionCodes] Failed to update session codes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('❌ Error while updating expired session codes: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
