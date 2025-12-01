<?php

namespace App\Http\Controllers;

use App\Services\Creator\ScanQr\ScanQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScanQrController extends Controller
{
    protected ScanQrService $scanQrService;

    public function __construct(ScanQrService $scanQrService)
    {
        $this->scanQrService = $scanQrService;
    }
    public function index(Request $request, $token)
    {
        $result = $this->scanQrService->scan($token);
        if ($result['success']) {
            $user = Auth::user();
            $sessionToken = session('qr_session_token');
            $ipAddress = $request->ip();

            Log::info('Scan Qr Info: ' . ($user?->id ?? 'guest') . ' - ' . $token . ' - ' . $sessionToken . ' - ' . $ipAddress);
            return redirect()->route('creator.qr-success');
        }

        return view('scan-error', [
            'message' => $result['message'],
            'title' => $result['title']
        ]);
    }
}
