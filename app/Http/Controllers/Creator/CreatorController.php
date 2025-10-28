<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Master\QrCode;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Services\Camera\LivePreviewService;
use App\Services\Creator\AddData\NewDataFormRequestService;
use App\Services\Creator\ScanQr\ScanQrService;
use App\Services\Support\GetModelService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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
        $token = $request->input('token');
        $result = $this->scanQrService->scan($token);

        if ($result['success']) {
            $user = Auth::user();
            $sessionToken = session('qr_session_token');
            $ipAddress = $request->ip();

            Log::info('Scan Qr Info: ' . ($user?->id ?? 'guest') . ' - ' . $token . ' - ' . $sessionToken . ' - ' . $ipAddress);
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => encryptData($result['data']),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => encryptData($result['message']),
            'data' => null,
        ]);
    }

    // ====== Check scanned QR ======
    public function checkScannedQr()
    {
        $scannedQr = $this->getActiveQrSession();

        if (!$scannedQr) {
            return $this->errorResponse('No QR data found, please scan again.', null);
        }

        $dataToSend = $scannedQr;

        return response()->json([
            'status' => 'success',
            'message' => 'QR data found.',
            'data' => encryptData($dataToSend),
        ]);
    }

    // ====== Add new data ======
    public function addNewData(Request $request, $type)
    {
        // $validated = $this->newDataFormRequestService->getValidatedData($type, $request);
        $userId = Auth::id();
        $ip = $request->ip();
        $scannedQrData = $this->getActiveQrSession();

        if (!$scannedQrData) {
            return $this->errorResponse('No active QR session found. Please scan a QR code first.', null);
        }

        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');
        $accessCode = $request->session_code;

        try {
            DB::beginTransaction();

            $qrCodeData = QrCode::where('qr_token', $sessionQrToken)
                ->where('is_active', 1)
                ->first();

            $sessionCodeQuery = SessionCode::where('generated_code', $accessCode)
                ->where('status', 'active')
                ->where('field_id', $qrCodeData->field_id);

            $sessionCode = $sessionCodeQuery->first();

            if (!$sessionCode) {
                DB::rollBack();
                return $this->errorResponse('Access code not found or inactive.', 404);
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $dataQuery = $modelClass::where('session_code_id', $sessionCode->id)
                ->where('field_id', $sessionCode->field_id);

            $data = $dataQuery->first();

            if (!$data) {
                DB::rollBack();
                return $this->errorResponse("Data {$type} for this session code not found.", 404);
            }

            if ($type === 'record') {
                $this->handleRecordData($request, $accessCode, $data, $scannedQrData, $sessionToken, $userId, $ip);
            } else {
                session(['stream_data_user' => $data->id]);
            }

            DB::commit();

            Log::channel('creator')->info("Data {$type} saved successfully", [
                'mode' => $type,
                'data' => $data,
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
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function handleRecordData($request, $accessCode, $data, $scannedQrData, $sessionToken, $userId, $ip)
    {
        if (!$accessCode) throw new \Exception('Access code is required.');

        $sessionCode = $this->getValidSessionCode($accessCode, $userId, $scannedQrData->qr_code_id, $data);

        $data->update([
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        RecordingLog::where('recording_id', $data->id)
            ->update([
                'qr_code' => $scannedQrData?->qrCode?->code,
                'updated_at' => Carbon::now(),
            ]);

        RecordSession::create([
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'recording_id' => $data->id,
            'qr_code' => $scannedQrData?->qrCode?->code,
            'status' => 'ongoing',
            'ip_address' => $ip,
        ]);

        $sessionCode->update([
            'user_id' => $userId,
            'qr_code_id' => $scannedQrData->qr_code_id,
            'recording_id' => $data->id,
            'session_token' => $sessionToken,
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
            'start_time' => $data->start_time,
            'end_time' => $data->end_time,
            'active_at' => now(),
            'inactive_at' => $data->end_time,
            'status' => 'ongoing',
        ]);
    }

    private function getValidSessionCode($accessCode, $userId, $qrCodeId, $data)
    {
        $sessionCode = SessionCode::where('generated_code', $accessCode)
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
        $sessionQrToken = session('qr_token');

        if (!$userId || !$sessionToken) return null;

        $query = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('qr_token', $sessionQrToken)
            ->latest();

        if ($requireActiveSession) {
            $query->whereNotNull('session_token');
            $query->whereNotNull('qr_token');
        }

        return $query->first();
    }

    private function errorResponse(string $message, ?int $code = 400)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        return is_null($code)
            ? response()->json($response)
            : response()->json($response, $code);
    }
}
