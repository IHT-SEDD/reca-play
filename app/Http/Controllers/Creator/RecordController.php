<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Master\Camera;
use App\Models\Record\Recording;
use App\Models\Session\SessionCode;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Services\Support\GetModelService;
use App\Services\Support\ResponseHelperService;
use App\Services\Support\SessionHelperService;
use App\Services\Creator\UtilityService;
use Illuminate\Support\Facades\DB;

class RecordController extends Controller
{
    // ============================================================
    // Init service
    // ============================================================
    protected GetModelService $getModelService;
    protected SessionHelperService $sessionHelperService;
    protected ResponseHelperService $responseHelperService;
    protected UtilityService $utilityService;

    public function __construct(
        GetModelService $getModelService,
        SessionHelperService $sessionHelperService,
        ResponseHelperService $responseHelperService,
        UtilityService $utilityService
    ) {
        $this->getModelService = $getModelService;
        $this->sessionHelperService = $sessionHelperService;
        $this->responseHelperService = $responseHelperService;
        $this->utilityService = $utilityService;
    }

    // ============================================================
    // Show view of record
    // ============================================================
    public function recordPage()
    {
        return view('pages.creator.record.index');
    }

    // ============================================================
    // Check data record
    // ============================================================
    public function checkData(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');
        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');
        $scannedQrData = $this->sessionHelperService->getActiveQrSession();

        Log::channel('camera-record')->info('[PREPARE RECORDING] Start checkData', [
            'user_id' => $userId,
            'type' => $type,
            'session_token' => session('qr_session_token')
        ]);

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();

            if (!$sessionCode) {
                return $this->responseHelperService->errorResponse(
                    'Session code not found or invalid.',
                    404
                );
            }

            $data = Recording::where('id', $sessionCode->recording_id)
                ->where('user_id', $userId)
                ->where('field_id', $sessionCode->field_id)
                ->where('session_code_id', $sessionCode->id)
                ->first();

            if (!$data) {
                return $this->responseHelperService->errorResponse(
                    'Recording data not found.',
                    404
                );
            }

            $fieldId = $data->field_id;
            $recordingId = $data->id;

            if (!$recordingId) {
                return $this->responseHelperService->errorResponse(
                    message: 'No record data found in session.',
                    redirect: url('/my-recording')
                );
            }

            $autoStopResponse = $this->utilityService->handleAutoStop($data, $fieldId, $type);
            if ($autoStopResponse) {
                return $autoStopResponse;
            }

            $cameraData = Camera::where('field_id', $fieldId)->get();

            DB::beginTransaction();

            $streamUrl = $this->utilityService->livePreview($fieldId);

            DB::commit();

            return $this->responseHelperService->successResponse(
                message: 'Record data fetched successfully.',
                data: [
                    'recordData' => $data,
                    'scannedQrData' => $scannedQrData,
                    'cameraData' => $cameraData,
                    'streamUrl' => $streamUrl,
                ]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-record')->error('[PREPARE RECORDING] Error', [
                'error' => $e->getMessage()
            ]);
            return $this->responseHelperService->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    // ============================================================
    // Stop recording (async queued jobs)
    // ============================================================
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $type = 'record';
        $sessionToken = session('qr_session_token');

        if (!$this->utilityService->isValidType($type)) {
            return $this->responseHelperService->errorResponse('Invalid or missing type parameter.', 400);
        }

        Log::channel('camera-record')->info("[STOP RECORD] Start stopRecording", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();
            if (!$sessionCode) {
                return $this->responseHelperService->errorResponse('Session code not found.', 404);
            }

            $data = Recording::where('id', $sessionCode->recording_id)
                ->where('user_id', $userId)
                ->where('field_id', $sessionCode->field_id)
                ->where('session_code_id', $sessionCode->id)
                ->first();

            if (!$data) {
                return $this->responseHelperService->errorResponse(
                    'Recording data not found.',
                    404
                );
            }

            if (in_array($data->status, ['done', 'processing'])) {
                Log::channel('camera-record')->warning("[STOP RECORDING] Already processed or in progress", [
                    'id' => $data->id,
                    'current_status' => $data->status,
                ]);

                return $this->responseHelperService->otherResponse(
                    status: 'skipped',
                    message: 'Recording already processed or still being processed.',
                    data: ['recordData' => $data],
                    code: 200
                );
            }

            $result = $this->utilityService->finalizeRecording(
                $data,
                $data->field_id,
                $userId,
                $sessionCode->id,
                $sessionToken,
                false,
                $type
            );

            if ($result['status'] === 'success') {
                return $this->responseHelperService->successResponse(
                    message: $result['message'],
                    data: ['recordData' => $result['data']]
                );
            }

            return $this->responseHelperService->errorResponse($result['message'], 500);
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error('[STOP RECORDING] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->responseHelperService->errorResponse($e->getMessage(), 500);
        }
    }
}
