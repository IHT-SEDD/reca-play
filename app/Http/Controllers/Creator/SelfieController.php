<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Master\Camera;
use App\Models\Record\Recording;
use App\Models\Selfie\Selfie;
use App\Models\Session\SessionCode;
use App\Services\Creator\UtilityService;
use App\Services\Support\GetModelService;
use App\Services\Support\ResponseHelperService;
use App\Services\Support\SessionHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelfieController extends Controller
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
    // Show view of selfie
    // ============================================================
    public function selfiePage()
    {
        return view('pages.creator.selfie.index');
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

        Log::channel('camera-selfie')->info('[PREPARE SELFIE] Start checkData', [
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

            $data = Selfie::where('id', $sessionCode->selfie_id)
                ->where('user_id', $userId)
                ->where('field_id', $sessionCode->field_id)
                ->where('session_code_id', $sessionCode->id)
                ->first();

            if (!$data) {
                return $this->responseHelperService->errorResponse(
                    'Selfie data not found.',
                    404
                );
            }

            $fieldId = $data->field_id;
            $selfieId = $data->id;

            if (!$selfieId) {
                return $this->responseHelperService->errorResponse(
                    message: 'No record data found in session.',
                    redirect: url('/')
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
                message: 'Selfie data fetched successfully.',
                data: [
                    'selfieData' => $data,
                    'scannedQrData' => $scannedQrData,
                    'cameraData' => $cameraData,
                    'streamUrl' => $streamUrl,
                ]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-selfie')->error('[PREPARE SELFIE] Error', [
                'error' => $e->getMessage()
            ]);
            return $this->responseHelperService->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
