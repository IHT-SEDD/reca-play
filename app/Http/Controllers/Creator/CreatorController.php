<?php

namespace App\Http\Controllers\Creator;

use App\Enums\MasterStatus;
use App\Enums\RecordSessionStatus;
use App\Enums\SelfieSessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Master\QrCode;
use App\Models\Record\RecordingLog;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Services\Camera\LivePreviewService;
use App\Services\Creator\AddData\NewDataFormRequestService;
use App\Services\Creator\ScanQr\ScanQrService;
use App\Services\Support\GetModelService;
use App\Services\Support\ResponseHelperService;
use App\Services\Support\SessionHelperService;
use App\Enums\SessionCodeStatus;
use App\Enums\SessionLogStatus;
use App\Enums\StreamSessionStatus;
use App\Models\Selfie\SelfieLog;
use App\Models\Session\QrSession;
use App\Models\Session\SelfieSession;
use App\Models\Session\StreamSession;
use App\Models\Stream\StreamingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatorController extends Controller
{
    // ============================================================
    // Init services
    // ============================================================
    protected ScanQrService $scanQrService;
    protected NewDataFormRequestService $newDataFormRequestService;
    protected GetModelService $getModelService;
    protected LivePreviewService $livePreviewService;
    protected SessionHelperService $sessionHelperService;
    protected ResponseHelperService $responseHelperService;

    public function __construct(
        ScanQrService $scanQrService,
        NewDataFormRequestService $newDataFormRequestService,
        GetModelService $getModelService,
        LivePreviewService $livePreviewService,
        SessionHelperService $sessionHelperService,
        ResponseHelperService $responseHelperService
    ) {
        $this->scanQrService = $scanQrService;
        $this->newDataFormRequestService = $newDataFormRequestService;
        $this->getModelService = $getModelService;
        $this->livePreviewService = $livePreviewService;
        $this->sessionHelperService = $sessionHelperService;
        $this->responseHelperService = $responseHelperService;
    }

    // ============================================================
    // Show view scan qr
    // ============================================================
    public function scanQrPage()
    {
        return view('pages.creator.qr.scan-qr');
    }

    // ============================================================
    // Show view after success scan qr
    // ============================================================
    public function scanSuccessPage()
    {

        return view('pages.creator.scan-success');
    }

    // ============================================================
    // Handling process of scanned qr
    // ============================================================
    public function scanQrProcess(Request $request)
    {
        $token = $request->input('token');
        $result = $this->scanQrService->scan($token);

        $user = Auth::user();
        $sessionToken = session('qr_session_token');
        $ipAddress = $request->ip();

        Log::channel('creator')->info('Scan Qr Info: ' . ($user?->id ?? 'guest') . ' - ' . $token . ' - ' . $sessionToken . ' - ' . $ipAddress);

        if ($result['success']) {
            return $this->responseHelperService->successResponse(
                $result['message'],
                encryptData($result['data'])
            );
        }

        return $this->responseHelperService->errorResponse(
            encryptData($result['message'])
        );
    }

    // ============================================================
    // Checking scanned qr
    // ============================================================
    public function checkScannedQr()
    {
        $scannedQr = $this->sessionHelperService->getActiveQrSession();

        if (!$scannedQr) {
            return $this->responseHelperService->errorResponse(
                'No QR data found, please scan again.'
            );
        }

        $dataToSend = $scannedQr;

        return $this->responseHelperService->successResponse(
            'QR data found.',
            encryptData($dataToSend)
        );
    }

    // ============================================================
    // Handling add new data of record or stream
    // ============================================================
    public function addNewData(Request $request, $type)
    {
        if (!$this->isValidType($type)) {
            return $this->responseHelperService->errorResponse('Invalid type parameter.', 400);
            Log::channel('creator')->error('Invalid type parameter');
        }

        $userId = Auth::id();
        $ip = $request->ip();
        $scannedQrData = $this->sessionHelperService->getActiveQrSession();

        if (!$scannedQrData) {
            return $this->responseHelperService->errorResponse(
                'No active QR session found. Please scan a QR code first.'
            );
            Log::channel('creator')->error('No active QR session found. Please scan a QR code first.');
        }

        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');
        $accessCode = $request->session_code;

        try {
            DB::beginTransaction();

            $qrCodeData = QrCode::where('qr_token', $sessionQrToken)
                ->where('is_active', MasterStatus::Active)
                ->first();

            $sessionCode = SessionCode::where('generated_code', $accessCode)
                ->where('status', SessionCodeStatus::Active)
                ->where('field_id', $qrCodeData->field_id)
                ->first();

            if (!$sessionCode) {
                DB::rollBack();
                return $this->responseHelperService->errorResponse(
                    'Access code not found or inactive.',
                    404
                );
                Log::channel('creator')->error('Access code not found or inactive');
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $data = $modelClass::where('session_code_id', $sessionCode->id)
                ->where('field_id', $sessionCode->field_id)
                ->first();

            if ($type === 'selfie') {
                $data->update([
                    'photo_name' => $request->pict_name,
                ]);
            }

            if (!$data) {
                DB::rollBack();
                return $this->responseHelperService->errorResponse(
                    "Data {$type} for this session code not found.",
                    404
                );
                Log::channel('creator')->error(`Data {$type} for this session code not found.`);
            }

            $this->handleDataByType($type, $accessCode, $data, $scannedQrData, $sessionToken, $userId, $ip);

            DB::commit();

            Log::channel('creator')->info("Data {$type} saved successfully", [
                'mode' => $type,
                'data' => $data,
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return $this->responseHelperService->successResponse(
                "Data {$type} saved successfully"
            );
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('creator')->error("Failed to save {$type} data", [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return $this->responseHelperService->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    // ============================================================
    // Handling process of new record data
    // ============================================================
    private function isValidType(?string $type): bool
    {
        return in_array($type, ['record', 'stream', 'selfie']);
    }

    private function getConfigByType(string $type): array
    {
        return match ($type) {
            'record' => [
                'logModel' => RecordingLog::class,
                'sessionModel' => RecordSession::class,
                'statusEnum' => RecordSessionStatus::Ongoing,
                'idField' => 'recording_id',
                'sessionStatus' => 'record',
            ],
            'stream' => [
                'logModel' => StreamingLog::class,
                'sessionModel' => StreamSession::class,
                'statusEnum' => StreamSessionStatus::Ongoing,
                'idField' => 'streaming_id',
                'sessionStatus' => 'stream',
            ],
            'selfie' => [
                'logModel' => SelfieLog::class,
                'sessionModel' => SelfieSession::class,
                'statusEnum' => SelfieSessionStatus::Ongoing,
                'idField' => 'selfie_id',
                'sessionStatus' => 'selfie',
            ],
        };
    }

    private function handleDataByType($type, $accessCode, $data, $scannedQrData, $sessionToken, $userId, $ip)
    {
        if (!$accessCode) {
            throw new \Exception('Access code is required.');
        }

        $config = $this->getConfigByType($type);
        $sessionCode = $this->sessionHelperService
            ->getValidAccessCode($accessCode, $userId, $scannedQrData->qr_code_id, $data);

        $data->update([
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        // Update log table
        $config['logModel']::where($config['idField'], $data->id)
            ->update([
                'qr_code' => $scannedQrData?->qrCode?->code,
                'updated_at' => Carbon::now(),
            ]);

        // Create session record
        $config['sessionModel']::create([
            'user_id' => $userId,
            'session_token' => $sessionToken,
            $config['idField'] => $data->id,
            'qr_code' => $scannedQrData?->qrCode?->code,
            'status' => $config['statusEnum'],
            'ip_address' => $ip,
        ]);

        $expireQrSession = Carbon::parse($data->end_time)->addMinutes(5);

        QrSession::where('session_token', $sessionToken)
            ->where('user_id', $userId)
            ->where('qr_code_id', $scannedQrData->qr_code_id)
            ->update([
                'expired_at' => $expireQrSession
            ]);

        // Update session code
        $sessionCode->update([
            'user_id' => $userId,
            'qr_code_id' => $scannedQrData->qr_code_id,
            // $config['idField'] => $data->id,
            'session_token' => $sessionToken,
            'status' => SessionCodeStatus::InUse,
            'used_at' => now(),
        ]);

        // Insert into session log
        SessionLog::create([
            'user_id' => $userId,
            'qr_code_id' => $scannedQrData->qr_code_id,
            'session_code_id' => $sessionCode->id,
            $config['idField'] => $data->id,
            'type' => $config['sessionStatus'],
            'session_token' => $sessionToken,
            'start_time' => $data->start_time,
            'end_time' => $data->end_time,
            'active_at' => now(),
            'inactive_at' => $data->end_time,
            'status' => SessionLogStatus::Ongoing,
        ]);
    }
}
