<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Services\Camera\LivePreviewService;
use App\Services\Creator\AddData\NewDataFormRequestService;
use App\Services\Creator\ScanQr\ScanQrService;
use App\Services\Support\GetModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatorController extends Controller
{
    protected ScanQrService $scanQrService;
    protected NewDataFormRequestService $newDataFormRequestService;
    protected GetModelService $getModelService;
    protected LivePreviewService $livePreviewService;

    // ====== Initialize services ======
    public function __construct(
        ScanQrService $scanQrService,
        NewDataFormRequestService $newDataFormRequestService,
        GetModelService $getModelService,
        LivePreviewService $livePreviewService
    ) {
        $this->scanQrService = $scanQrService;
        $this->newDataFormRequestService = $newDataFormRequestService;
        $this->getModelService = $getModelService;
        $this->livePreviewService = $livePreviewService;
    }

    // ====== Show page of scan QR ======
    public function scanQrPage()
    {
        return view('pages.creator.qr.scan-qr');
    }

    // ====== Show page after success scan QR ======
    public function scanSuccessPage()
    {

        return view('pages.creator.scan-success');
    }

    // ====== Show live stream page ======
    public function liveStreamPage()
    {
        return view('pages.creator.live-stream.index');
    }

    // ====== Process the scanned QR ======
    public function scanQrProcess(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $result = $this->scanQrService->scan($request->token);

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => $result['data'] ?? null
        ], $result['success'] ? 200 : 400);
    }

    // ====== Check scanned QR ======
    public function checkScannedQr()
    {
        $scannedQr = $this->getActiveQrSession();

        if (!$scannedQr) {
            return $this->errorResponse('No QR data found, please scan again.', 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'QR data found.',
            'data' => $scannedQr
        ]);
    }

    // ====== Add new data ======
    public function addNewData(Request $request, $type)
    {
        $validated = $this->newDataFormRequestService->getValidatedData($type, $request);
        $userId = Auth::id();
        $ip = $request->ip();
        $scannedQrData = $this->getActiveQrSession();

        if (!$scannedQrData) {
            return $this->errorResponse('No active QR session found. Please scan a QR code first.', 400);
        }

        $sessionToken = session('qr_session_token');

        try {
            DB::beginTransaction();

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $data = $modelClass::create($validated);

            if ($type === 'record') {
                $this->handleRecordData($request, $data, $scannedQrData, $sessionToken, $userId, $ip);
            } else {
                session(['stream_data_user' => $data->id]);
            }

            DB::commit();

            Log::channel('creator')->info("Data {$type} saved successfully", [
                'mode' => $type,
                'data' => $validated,
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Data {$type} saved successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('creator')->error("Failed to save data {$type}", [
                'mode' => $type,
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function handleRecordData($request, $data, $scannedQrData, $sessionToken, $userId, $ip)
    {
        $inputCode = $request->input('session_code');
        if (!$inputCode) throw new \Exception('Session code is required.');

        $sessionCode = $this->getValidSessionCode($inputCode, $userId, $scannedQrData->qr_code_id);

        RecordingLog::create([
            'recording_id' => $data->id,
            'qr_code' => $scannedQrData?->qrCode?->code,
            'status' => 'prepare',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        RecordSession::create([
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'recording_id' => $data->id,
            'qr_code' => $scannedQrData?->qrCode?->code,
            'status' => 'prepare',
            'ip_address' => $ip,
        ]);

        $sessionCode->update([
            'user_id' => $userId,
            'qr_code_id' => $scannedQrData->qr_code_id,
            'recording_id' => $data->id,
            'session_token' => $sessionToken,
            'type' => 'record',
            'status' => 'in use',
            'used_at' => now(),
        ]);

        SessionLog::create([
            'user_id' => $userId,
            'qr_code_id' => $scannedQrData->qr_code_id,
            'session_code_id' => $sessionCode->id,
            'recording_id' => $data->id,
            'type' => 'record',
            'session_token' => $sessionToken,
            'active_at' => now(),
            'status' => 'ongoing',
        ]);
    }

    private function getValidSessionCode($inputCode, $userId, $qrCodeId)
    {
        $sessionCode = SessionCode::where('generated_code', $inputCode)
            ->where('status', '!=', 'expired')
            ->first();

        if (!$sessionCode) throw new \Exception('Session code not found! Please go to cashier and ask for the access code.');

        if ($sessionCode->expired_at && now()->greaterThan($sessionCode->expired_at)) {
            $sessionCode->update(['status' => 'expired']);
            throw new \Exception('Session code has expired. Please ask cashier for a new code.');
        }

        if ($sessionCode->user_id && $sessionCode->user_id !== $userId) {
            throw new \Exception('This session code is already in use by another user.');
        }

        return $sessionCode;
    }

    private function getActiveQrSession(bool $requireActiveSession = true): ?QrSession
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        if (!$userId || !$sessionToken) return null;

        $query = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest();

        if ($requireActiveSession) {
            $query->whereNotNull('session_token');
        }

        return $query->first();
    }

    private function errorResponse(string $message, int $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }
}
