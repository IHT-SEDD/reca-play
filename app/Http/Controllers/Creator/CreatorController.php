<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordingLog;
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

    // ====== Initialize services ======
    public function __construct(
        ScanQrService $scanQrService,
        NewDataFormRequestService $newDataFormRequestService,
        GetModelService $getModelService
    ) {
        $this->scanQrService = $scanQrService;
        $this->newDataFormRequestService = $newDataFormRequestService;
        $this->getModelService = $getModelService;
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

    // ====== Show record page ======
    public function recordPage()
    {
        return view('pages.creator.record.index');
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
        $scannedQr = session('scanned_qr');

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
        // dd($request->all());
        $validated = $this->newDataFormRequestService->getValidatedData($type, $request);
        $userId = Auth::id();
        $ip = $request->ip();
        $scannedQrData = session('scanned_qr');
        $qrCode = $scannedQrData['code'] ?? null;

        try {
            DB::beginTransaction();

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $data = $modelClass::create($validated);

            if ($type == 'record') {
                RecordingLog::create([
                    'recording_id' => $data->id,
                    'qr_code' => $qrCode,
                    'status' => 'ongoing',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
            };

            DB::commit();

            Log::channel('creator')->info("Data {$type} saved successfully", [
                'mode' => $type,
                'data' => $validated,
                'user_id' => $userId,
                'ip' => $ip,
            ]);

            return response()->json([
                'status'  => 'success',
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
