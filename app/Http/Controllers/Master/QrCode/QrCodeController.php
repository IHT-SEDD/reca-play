<?php

namespace App\Http\Controllers\Master\QrCode;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrCodeController extends Controller
{
    /**
     * Handle QR Code download by filename.
     *
     * @param string $filename
     * @return StreamedResponse|JsonResponse
     */
    public function download(string $filename)
    {
        $path = "qr_codes/{$filename}";

        try {
            if (!Storage::disk('public')->exists($path)) {
                Log::channel('qr_code_download')->warning("QR Code file not found", [
                    'filename' => $filename,
                    'path' => $path,
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => "QR Code file not found.",
                ], 404);
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            Log::channel('qr_code_download')->info("QR Code downloaded successfully", [
                'filename' => $filename,
                'path' => $path,
                'time' => now()->toDateTimeString(),
            ]);

            return $disk->download($path, $filename);
        } catch (\Throwable $e) {
            Log::channel('qr_code_download')->error("QR Code download error", [
                'filename' => $filename,
                'path' => $path,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => "Failed to download QR Code. Please try again later.",
            ], 500);
        }
    }
}
