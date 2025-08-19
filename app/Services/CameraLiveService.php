<?php

namespace App\Services;

class CameraLiveService
{
 public function getLiveStreamUrl()
 {
  // HLS URL kamera
  return "http://127.0.0.1:8888/camera1/index.m3u8";
 }
}
