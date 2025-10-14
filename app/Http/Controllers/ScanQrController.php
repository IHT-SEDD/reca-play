<?php

namespace App\Http\Controllers;

use App\Services\Creator\ScanQr\ScanQrService;
use Illuminate\Http\Request;

class ScanQrController extends Controller
{
    protected ScanQrService $scanQrService;

    public function __construct(ScanQrService $scanQrService)
    {
        $this->scanQrService = $scanQrService;
    }
    public function index($token)
    {
        session(['qr_token' => $token]);

        $result = $this->scanQrService->scan($token);
        if ($result['success']) {
            return redirect()->route('creator.qr-success');
        }

        return view('scan-error', [
            'message' => $result['message'],
            'title' => $result['title']
        ]);
    }
}
