<?php

namespace App\Http\Controllers\Api\Button;

use App\Http\Controllers\Controller;
use App\Jobs\Highlight\GetPlaybackHighlightUrisJob;
use App\Models\Hightlight\ButtonLog;
use App\Models\Master\Api;
use App\Models\Master\Field;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Services\Support\ResponseHelperService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HighlightController extends Controller
{
    // ============================================================
    // Init service
    // ============================================================
    protected ResponseHelperService $responseHelperService;

    public function __construct(ResponseHelperService $responseHelperService)
    {
        $this->responseHelperService = $responseHelperService;
    }

    // ============================================================
    // Send highlight button data
    // ============================================================
    public function sendData(Request $request)
    {
        Log::channel('highlight-button')->info('[SEND DATA] Incoming request', [
            'payload' => $request->all()
        ]);

        // Uncomment for debugging pressed_at data from button
        if (!app()->environment('production')) {
            return response()->json([
                'raw_input' => $request->pressed_at,
                'raw' => strtotime($request->pressed_at) - time() - 1,
                'pressed_at' => strtotime($request->pressed_at),
                'server_ts' => time(),
                'parsed' => date('Y-m-d H:i:s', strtotime($request->pressed_at)),
                'server_now' => date('Y-m-d H:i:s'),
            ]);
        }

        $validatedData = $this->validateSendData($request);

        DB::beginTransaction();

        try {
            $validation = $this->validateBeforeInsert($validatedData);

            if (!is_array($validation) || !isset($validation['api'])) {
                Log::channel('highlight-button')->warning('[SEND DATA] Validation failed', [
                    'response' => $validation
                ]);
                return $validation;
            }

            $apiData = $validation['api'];
            $fieldData = $validation['field'];
            $pressedAt = $validation['pressed_at'];
            $highlight_start = $validation['highlight_start'];
            $highlight_end = $validation['highlight_end'];

            $sessionCode = SessionCode::where('field_id', $fieldData->id)
                ->whereNotNull('recording_id')
                ->latest('id')
                ->first();

            if (!$sessionCode) {
                Log::channel('highlight-button')->warning('[SEND DATA] No session code found for field', [
                    'field_id' => $fieldData->id
                ]);
            } elseif (!$sessionCode->name) {
                Log::channel('highlight-button')->warning('[SEND DATA] Session code found but missing name', [
                    'field_id' => $fieldData->id,
                    'session_code_id' => $sessionCode->id
                ]);
            }

            $videoName = $this->generateDefaultVideoName($highlight_end);

            if ($sessionCode && $sessionCode->name) {
                $videoName = $sessionCode->name . '_highlight_' . date('Ymd_His', strtotime($highlight_end));
            }

            Log::channel('highlight-button')->info('[SEND DATA] Insert ButtonLog', [
                'api_id' => $apiData->id,
                'field_id' => $fieldData->id,
                'pressed_at' => $pressedAt,
                'highlight_start' => $highlight_start,
                'highlight_end' => $highlight_end
            ]);

            $buttonLog = ButtonLog::create([
                'api_id' => $apiData->id,
                'field_id' => $fieldData->id,
                'button_status' => $validatedData['button_status'],
                'pressed_at' => $pressedAt,
                'highlight_start' => $highlight_start,
                'highlight_end' => $highlight_end,
            ]);

            DB::commit();

            GetPlaybackHighlightUrisJob::dispatch(
                $fieldData->id,
                $sessionCode?->user_id,
                $sessionCode?->recording_id,
                $videoName,
                $highlight_start,
                $highlight_end
            )->onQueue('camera-highlight-video-search');

            Log::channel('highlight-button')->info('[SEND DATA] Success insert button log', [
                'button_log_id' => $buttonLog->id
            ]);

            return $this->responseHelperService->successResponse(
                message: 'Button data logged successfully.',
                data: ['button_log' => $buttonLog]
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::channel('highlight-button')->error('[SEND DATA] Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->responseHelperService->errorResponse(
                message: 'Failed to log button data. ' . $e->getMessage(),
                code: 500
            );
        }
    }

    // ============================================================
    // Get highlight button data
    // ============================================================
    public function getData(Request $request)
    {
        Log::channel('highlight-button')->info('[GET DATA] Request received', [
            'payload' => $request->all()
        ]);

        try {
            $apiData = Api::where('name', $request->api_name)
                ->where('username', $request->api_username)
                ->where('password', $request->api_password)
                ->first();

            if (!$apiData) {
                Log::channel('highlight-button')->warning('[GET DATA] Invalid API credentials');
                return $this->responseHelperService->errorResponse(
                    message: 'Invalid API credentials.',
                    code: 401
                );
            }

            $lastButtonLog = ButtonLog::latest()->first();

            if (!$lastButtonLog) {
                Log::channel('highlight-button')->info('[GET DATA] No ButtonLog found');
                return $this->responseHelperService->successResponse(
                    message: 'Nothing button log data found.'
                );
            }

            Log::channel('highlight-button')->info('[GET DATA] Last ButtonLog retrieved', [
                'button_log_id' => $lastButtonLog->id
            ]);

            return $this->responseHelperService->successResponse(
                message: 'Button log retrieved successfully.',
                data: ['button_log' => $lastButtonLog]
            );
        } catch (Exception $e) {
            Log::channel('highlight-button')->error('[GET DATA] Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->responseHelperService->errorResponse(
                message: 'Failed to retrieve button log data. ' . $e->getMessage(),
                code: 500
            );
        }
    }

    // ============================================================
    // Private function to validate sendData request
    // ============================================================
    private function validateSendData(Request $request)
    {
        return $request->validate([
            'api_name' => 'required|string|min:5',
            'api_username' => 'required|string|min:5',
            'api_password' => 'required|string|min:5',
            'field' => 'required|string|min:5',
            'button_status' => 'required|string|in:pressed,unpressed',
            'pressed_at' => [
                'nullable',
                'regex:/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'
            ]
        ], [
            'pressed_at.regex' => 'pressed_at format must be YYYY-MM-DD HH:MM:SS',
        ]);
    }

    // ============================================================
    // Private function to validate before insert
    // ============================================================
    private function validateBeforeInsert(array $data)
    {
        // ===== Validate API credentials =====
        $apiData = Api::where('name', $data['api_name'])
            ->where('username', $data['api_username'])
            ->where('password', $data['api_password'])
            ->first();

        if (!$apiData) {
            Log::channel('highlight-button')->warning('[VALIDATION] Invalid API credentials');
            return $this->responseHelperService->errorResponse(
                message: 'Invalid API credentials.',
                code: 401
            );
        }

        // ===== Button must be pressed =====
        if ($data['button_status'] === 'unpressed') {
            Log::channel('highlight-button')->warning('[VALIDATION] Button is unpressed');
            return $this->responseHelperService->errorResponse(
                message: 'Button must be pressed to send data.',
                code: 401
            );
        }

        // ===== Validate Field =====
        $fieldData = Field::where('code', $data['field'])
            ->where('is_active', true)
            ->first();

        if (!$fieldData) {
            Log::channel('highlight-button')->warning('[VALIDATION] Field invalid', [
                'field' => $data['field']
            ]);
            return $this->responseHelperService->errorResponse(
                message: 'Field not found or inactive.',
                code: 404
            );
        }

        // ===== Validate pressed_at timestamp =====
        $pressedAt = now();

        if ($pressedAt === false) {
            return $this->responseHelperService->errorResponse(
                message: 'Invalid pressed_at datetime format.',
                code: 400
            );
        }

        // if ($pressedAt > time()) {
        //     return $this->responseHelperService->errorResponse(
        //         message: 'pressed_at cannot be greater than current timestamp.',
        //         code: 400
        //     );
        // }

        // ===== Check existing highlight conflict =====
        $lastLog = ButtonLog::where('field_id', $fieldData->id)
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastLog) {
            $minAllowedPressedAt = \Carbon\Carbon::parse($lastLog->highlight_end)
                ->addSeconds(30);

            if ($pressedAt->lessThanOrEqualTo($minAllowedPressedAt)) {
                return $this->responseHelperService->errorResponse(
                    message: 'Current highlight is in process.',
                    code: 400
                );
            }
        }

        // ===== Compute highlight timestamps =====
        $highlight_start = $pressedAt->copy()->subSeconds(30)->format('Y-m-d H:i:s');
        $highlight_end   = $pressedAt->format('Y-m-d H:i:s');

        return [
            'api' => $apiData,
            'field' => $fieldData,
            'pressed_at' => $data['pressed_at'],
            'highlight_start' => $highlight_start,
            'highlight_end' => $highlight_end,
        ];
    }

    // ============================================================
    // Private function to generate random code
    // ============================================================
    private function generateRandomCode(): string
    {
        $digits = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $lettersPool = range('A', 'Z');
        $letters = '';
        for ($i = 0; $i < 3; $i++) {
            $letters .= $lettersPool[random_int(0, count($lettersPool) - 1)];
        }

        return $digits . $letters;
    }

    // ============================================================
    // Private function to generate default video name
    // ============================================================
    private function generateDefaultVideoName(string $highlightEnd): string
    {
        $suffix = date('ymd', strtotime($highlightEnd));

        $code = $this->generateRandomCode();

        return "highlight_{$code}_{$suffix}";
    }
}
