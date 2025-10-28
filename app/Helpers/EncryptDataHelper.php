<?php

use Illuminate\Support\Facades\Crypt;

if (!function_exists('encryptData')) {
 function encryptData($data)
 {
  if (!config('app.debug') || app()->environment('production')) {
   return Crypt::encrypt($data);
  }
  return $data;
 }
}

if (!function_exists('decryptData')) {
 function decryptData($encryptedData)
 {
  if (!config('app.debug') || app()->environment('production')) {
   return Crypt::decrypt($encryptedData);
  }
  return $encryptedData;
 }
}
