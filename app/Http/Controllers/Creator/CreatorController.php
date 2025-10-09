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
        $request->validate([
            'token' => 'required|string',
        ]);

        $result = $this->scanQrService->scan($request->token);

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $result['message'],
            'data' => $result['data']
        ]);
    }

    // ====== Check scanned QR ======
    public function checkScannedQr()
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        $scannedQr = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();

        // $scannedQr = session('scanned_qr');

        if (!$scannedQr) {
            return response()->json([
                'status' => 'error',
                'message' => 'No QR data found, please scan again.'
            ], 400);
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
        // $scannedQrData = session('scanned_qr');
        $sessionToken = session('qr_session_token');

        $scannedQrData = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest()
            ->first();

        $qrCode = $scannedQrData?->qrCode?->code;

        try {
            DB::beginTransaction();

            // === Save the main data ===
            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            // dd($validated);
            $data = $modelClass::create($validated);

            if ($type == 'record') {
                if (!$scannedQrData) {
                    throw new \Exception('QR session not found, please scan again.');
                }

                $inputCode = $request->input('session_code');
                if (!$inputCode) {
                    throw new \Exception('Session code is required.');
                }

                $sessionCode = SessionCode::where('generated_code', $inputCode)
                    ->where('status', '!=', 'expired')
                    ->first();

                if (!$sessionCode) {
                    throw new \Exception('Session code not found! please go to cashier and ask for the access code.');
                }

                if ($sessionCode->expired_at && now()->greaterThan($sessionCode->expired_at)) {
                    $sessionCode->update(['status' => 'expired']);
                    throw new \Exception('Session code has expired. Please ask cashier for a new code.');
                }

                if ($sessionCode->user_id && $sessionCode->user_id !== $userId) {
                    throw new \Exception('This session code is already in use by another user.');
                }

                RecordingLog::create([
                    'recording_id' => $data->id,
                    'qr_code' => $qrCode,
                    'status' => 'prepare',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \App\Models\Session\RecordSession::create([
                    'user_id' => $userId,
                    'session_token' => $sessionToken,
                    'recording_id' => $data->id,
                    'qr_code' => $qrCode,
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

                // session(['record_data_user' => $data->id]);
            } else {
                session(['stream_data_user' => $data->id]);
            };

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
            DB::rollback();
            Log::channel('creator')->error("Failed to save data {$type}", [
                'mode' => $type,
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $ip,
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
