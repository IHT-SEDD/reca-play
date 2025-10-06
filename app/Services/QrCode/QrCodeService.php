<?php

namespace App\Services\QrCode;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
 /**
  * Generate QR Code image, save to storage, and return the path.
  *
  * @param string $qrData
  * @param string $fileName
  * @return string $path
  */
 public function generate($qrData, string $fileName): string
 {
  $filePath = 'qr_codes/' . $fileName . '.png';

  try {
   $result = Builder::create()
    ->writer(new PngWriter())
    ->data($qrData)
    ->size(300)
    ->margin(10)
    ->build();

   Storage::disk('public')->put($filePath, $result->getString());

   Log::channel('qr_code_generator')->info("QR generated successfully", [
    'file' => $filePath,
    'data' => $qrData,
   ]);

   return 'storage/' . $filePath;
  } catch (\Throwable $e) {
   Log::channel('qr_code_generator')->error("QR generation failed", [
    'file' => $filePath,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
   ]);

   throw $e;
  }
 }
}
