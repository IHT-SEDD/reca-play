<?php

namespace App\Services\Creator\ScanQr;

use App\Models\Master\QrCode;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    $qrCode = QrCode::select(['id', 'field_id', 'venue_id', 'code', 'name', 'type', 'qr_token', 'is_active'])
      ->with([
        'venue:id,name',
        'field:id,venue_id,name',
        'field.venue:id,name'
      ])
      ->where('qr_token', $token)
      ->first();

    if (!$qrCode) {
      return [
        'success' => false,
        'title' => 'QR code not found',
        'message' => 'QR code not found or invalid token.',
      ];
    }

    if ($qrCode->is_active == 0) {
      return [
        'success' => false,
        'title' => 'QR code inactive',
        'message' => 'QR code not active, please contact the administrator',
      ];
    }

    $existingSession = QrSession::whereNotNull('user_id')
      ->where('qr_code_id', $qrCode->id)
      ->whereNotNull('session_token')
      ->where('user_id', '!=', Auth::id())
      ->first();

    if ($existingSession) {
      return [
        'success' => false,
        'title' => 'Field in use',
        'message' => 'This field is currently in use. Please wait until the previous session is finished.',
      ];
    }

    $user = Auth::user();
    $userId = $user?->id;

    $sessionToken = Str::uuid()->toString();

    QrSession::create([
      'user_id' => $userId,
      'session_token' => $sessionToken,
      'qr_token' => $token,
      'qr_code_id' => $qrCode->id,
      'type' => $qrCode->type,
      'last_active_at' => now(),
    ]);

    session([
      'qr_token' => $token,
      'qr_session_token' => $sessionToken
    ]);

    return [
      'success' => true,
      'message' => 'QR code valid.',
      'data' => $qrCode,
    ];
  }

  public function getUserActiveSession(int $userId): ?QrSession
  {
    return QrSession::where('user_id', $userId)
      ->whereNotNull('session_token')
      ->latest('last_active_at')
      ->first();
  }
}
