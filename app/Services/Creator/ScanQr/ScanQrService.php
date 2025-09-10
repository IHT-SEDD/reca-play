<?php

namespace App\Services\Creator\ScanQr;

use App\Models\Master\QrCode;

class ScanQrService
{
  /**
   * Process the scanned QR token.
   *
   * @param string $token
   * @return array
   */
  public function scan(string $token): array
  {
    // Find QR code by token
    $qrCode = QrCode::select(['id', 'field_id', 'venue_id', 'code', 'name', 'type', 'qr_token', 'is_active'])
      ->with([
        'venue:id,name',
        'field:id,venue_id,name',
        'field.venue:id,name'
      ])
      ->where('qr_token', $token)
      ->first();

    // Return error if token QR not found
    if (!$qrCode) {
      return [
        'success' => false,
        'message' => 'QR code not found or invalid token.',
      ];
    }

    // Return error if QR status is not acitve
    if ($qrCode->is_active == 0) {
      return [
        'success' => false,
        'message' => 'QR code not active, please contact the administrator',
      ];
    }

    session([
      'scanned_qr' => $qrCode
    ]);

    return [
      'success' => true,
      'message' => 'QR code valid.',
      'data' => $qrCode,
    ];
  }
}
