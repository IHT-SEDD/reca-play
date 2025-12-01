<?php

namespace App\Console\Commands;

use App\Models\Record\Recording;
use App\Models\Session\SessionCode;
use App\Services\Creator\UtilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoStopRecording extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recording:auto-stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically stop recordings whose end_time has passed.';

    protected $utilityService;

    public function __construct(UtilityService $utilityService)
    {
        parent::__construct();
        $this->utilityService = $utilityService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('camera-record-auto-stop-cron')->info("[CRON] AutoStopRecording triggered");

        $now = Carbon::now();

        $recordings = Recording::where('end_time', '<=', $now)
            ->whereNotIn('status', ['done', 'processing'])
            ->get();

        if ($recordings->isEmpty()) {
            Log::channel('camera-record-auto-stop-cron')->info("[CRON] No recording to stop.");
            return;
        }

        foreach ($recordings as $record) {
            Log::channel('camera-record-auto-stop-cron')->info("[CRON] Processing auto-stop", [
                'recording_id' => $record->id,
                'user_id' => $record->user_id ?? null,
                'field_id' => $record->field_id,
                'end_time' => $record->end_time,
            ]);

            $sessionCode = SessionCode::find($record->session_code_id);

            if (!$sessionCode) {
                Log::channel('camera-record-auto-stop-cron')->error("[CRON] SessionCode NOT FOUND", [
                    'recording_id' => $record->id
                ]);
                continue;
            }

            try {
                $result = $this->utilityService->finalizeRecording(
                    $record,
                    $record->field_id,
                    $record->user_id ?? null,
                    $sessionCode->id,
                    $sessionCode->session_token,
                    true,
                    'record'
                );

                Log::channel('camera-record-auto-stop-cron')->info("[CRON] Finalize result", [
                    'recording_id' => $record->id,
                    'result' => $result
                ]);
            } catch (\Throwable $e) {
                Log::channel('camera-record-auto-stop-cron')->error("[CRON] Auto-stop failed", [
                    'recording_id' => $record->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
}
