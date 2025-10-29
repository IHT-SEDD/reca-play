<?php

namespace App\Services\Support;

use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Enums\SessionCodeStatus;
use Illuminate\Support\Facades\Auth;

class SessionHelperService
{
  // ============================================================
  // Handling active QR session
  // ============================================================
  public function getActiveQrSession(bool $requireActiveSession = true): ?QrSession
  {
    $userId = Auth::id();
    $sessionToken = session('qr_session_token');
    $sessionQrToken = session('qr_token');

    if (!$userId || !$sessionToken) {
      return null;
    }

    $query = QrSession::with(['qrCode.field.venue'])
      ->where('user_id', $userId)
      ->where('session_token', $sessionToken)
      ->where('qr_token', $sessionQrToken)
      ->latest();

    if ($requireActiveSession) {
      $query->whereNotNull('session_token')
        ->whereNotNull('qr_token');
    }

    return $query->first();
  }

  // ============================================================
  // Handling valid access code
  // ============================================================
  public function getValidAccessCode(string $accessCode, int $userId, ?int $qrCodeId = null, $data = null): SessionCode
  {
    $sessionCode = SessionCode::where('generated_code', $accessCode)
      ->whereNot('status', SessionCodeStatus::Expired)
      ->first();

    if (!$sessionCode) {
      throw new \Exception('Session code not found! Please go to cashier and ask for the access code.');
    }

    if ($sessionCode->expired_at && now()->greaterThan($sessionCode->expired_at)) {
      $sessionCode->update(['status' => SessionCodeStatus::Expired]);
      throw new \Exception('Session code has expired. Please ask cashier for a new code.');
    }

    if ($sessionCode->user_id && $sessionCode->user_id !== $userId) {
      throw new \Exception('This session code is already in use by another user.');
    }

    return $sessionCode;
  }
}
