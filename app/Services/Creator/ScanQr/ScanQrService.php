<?php

namespace App\Services\Creator\ScanQr;

use App\Models\Master\QrCode;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    DB::beginTransaction();

    try {
      $qrCode = QrCode::select(['id', 'field_id', 'venue_id', 'code', 'name', 'type', 'qr_file', 'qr_token', 'is_active'])
        ->with([
          'venue:id,name',
          'field:id,venue_id,name,is_active',
          'field.venue:id,name'
        ])
        ->where('qr_token', $token)
        ->first();

      if (!$qrCode) {
        DB::rollBack();
        Log::channel('creator')->warning('QR code not found or invalid token.', [
          'token' => $token,
          'user' => Auth::id(),
        ]);

        return [
          'success' => false,
          'title' => 'QR code not found',
          'message' => 'QR code not found or invalid token.',
        ];
      }

      if ($qrCode->field && $qrCode->field->is_active == 0) {
        DB::rollBack();
        Log::channel('creator')->warning('This field is currently not active. Please contact the administrator.', [
          'token' => $token,
          'field_id' => $qrCode->field_id,
          'qr_code_id' => $qrCode->id,
          'user' => Auth::id(),
        ]);

        return [
          'success' => false,
          'title' => 'Field inactive',
          'message' => 'This field is currently not active. Please contact the administrator.',
        ];
      }

      if ($qrCode->is_active == 0) {
        DB::rollBack();
        Log::channel('creator')->warning('QR code not active, please contact the administrator', [
          'token' => $token,
          'qr_code_id' => $qrCode->id,
          'user' => Auth::id(),
        ]);

        return [
          'success' => false,
          'title' => 'QR code inactive',
          'message' => 'QR code not active, please contact the administrator',
        ];
      }

      $user = Auth::user();

      $existingSession = QrSession::whereNotNull('user_id')
        ->where('qr_code_id', $qrCode->id)
        ->whereNotNull('session_token')
        ->where('user_id', '!=', Auth::id())
        ->first();

      if ($existingSession) {
        DB::rollBack();
        Log::channel('creator')->warning('Field in use', [
          'token' => $token,
          'qr_code_id' => $qrCode->id,
          'active_user_id' => $existingSession->user_id,
          'current_user_id' => Auth::id(),
        ]);

        return [
          'success' => false,
          'title' => 'Field in use',
          'message' => 'This field is currently in use. Please wait until the previous session is finished.',
        ];
      }

      $sessionToken = session('qr_session_token');
      if (!$sessionToken) {
        $sessionToken = Str::uuid()->toString();
        session(['qr_session_token' => $sessionToken]);
      }

      $userExistingSession = QrSession::where('session_token', $sessionToken)
        ->where('user_id', $user?->id)
        ->first();

      if ($userExistingSession) {
        $userExistingSession->update([
          'qr_token' => $token,
          'qr_code_id' => $qrCode->id,
          'type' => $qrCode->type,
          'last_active_at' => now(),
          'updated_at' => now(),
        ]);

        session([
          'qr_token' => $token,
          'qr_session_token' => $sessionToken
        ]);

        DB::commit();
        return [
          'success' => true,
          'message' => 'Session already active.',
          'data'    => $qrCode,
          'session' => $userExistingSession,
        ];
      }

      $newSession = QrSession::create([
        'user_id' => $user?->id,
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

      DB::commit();

      Log::channel('creator')->info('QR code scanned', [
        'qr_id' => $qrCode->id,
        'field' => $qrCode->field->name,
        'token' => $qrCode->qr_token,
        'file' => $qrCode->qr_file,
        'user' => Auth::id(),
        'session_token' => session('qr_session_token') ?? $sessionToken,
      ]);

      return [
        'success' => true,
        'message' => 'QR code valid.',
        'data' => $qrCode,
        'session' => $newSession,
      ];
    } catch (\Throwable $e) {
      DB::rollBack();
      Log::error('Scan QR failed', [
        'token' => $token,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'user_id' => Auth::id(),
      ]);

      return [
        'success' => false,
        'title' => 'System Error',
        'message' => 'An error occurred while processing the QR code.',
      ];
    }
  }
}
