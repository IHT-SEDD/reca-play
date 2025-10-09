<?php

namespace App\Services\Creator\ScanQr;

use App\Models\Master\QrCode;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    $user = Auth::user();

    // session(['scanned_qr' => $qrCode]);

    $sessionToken = Str::uuid()->toString();

    QrSession::create([
      'user_id' => $user->id,
      'session_token' => $sessionToken,
      'qr_code_id' => $qrCode->id,
      'qr_code' => $qrCode->code,
      'type' => $qrCode->type,
      'qr_data' => $qrCode->toArray(),
      'last_active_at' => now(),
    ]);

    session(['qr_session_token' => $sessionToken]);

    return [
      'success' => true,
      'message' => 'QR code valid.',
      'data' => $qrCode,
    ];
  }
}
