<?php

namespace App\Services\Support;

use Illuminate\Http\JsonResponse;

class ResponseHelperService
{
  // ============================================================
  // Error response
  // ============================================================
  public function errorResponse(string $message, ?int $code = 400, ?string $redirect = null): JsonResponse
  {
    $response = [
      'status' => 'error',
      'message' => $message,
    ];

    if (!is_null($redirect)) {
      $response['redirect'] = $redirect;
    }

    return response()->json($response, $code ?? 400);
  }

  // ============================================================
  // Success response
  // ============================================================
  public function successResponse(string $message, mixed $data = null, int $code = 200, ?string $redirect = null): JsonResponse
  {
    $response = [
      'status' => 'success',
      'message' => $message,
    ];

    // If $data is associative array, merge directly into root level
    if (is_array($data)) {
      $response = array_merge($response, $data);
    } elseif (!is_null($data)) {
      $response['data'] = $data;
    }

    if (!is_null($redirect)) {
      $response['redirect'] = $redirect;
    }

    return response()->json($response, $code);
  }

  // ============================================================
  // Other response (Flexible: custom status, message, data, redirect, code)
  // ============================================================
  public function otherResponse(
    string $status,
    string $message,
    mixed $data = null,
    ?int $code = null,
    ?string $redirect = null
  ): JsonResponse {

    $response = [
      'status' => $status,
      'message' => $message,
    ];

    if (is_array($data)) {
      $response = array_merge($response, $data);
    } elseif (!is_null($data)) {
      $response['data'] = $data;
    }

    if (!is_null($redirect)) {
      $response['redirect'] = $redirect;
    }

    if (is_null($code)) {
      $code = in_array(strtolower($status), ['success', 'ok']) ? 200 : 400;
    }

    return response()->json($response, $code);
  }
}
