<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class VideoProcessingService
{
  /**
   * Convert video ke MP4 (misal input .dav atau RTSP)
   * 
   * @param string $inputPath RTSP URL atau path file relatif di storage/public
   * @param string $outputPath Path full output MP4
   * @throws ProcessFailedException
   */
  public function convertToMp4(string $inputPath, string $outputPath): void
  {
    // Tentukan input
    $input = stripos($inputPath, 'rtsp://') === 0
      ? $inputPath
      : Storage::disk('public')->path($inputPath);

    $output = $outputPath;

    $process = new Process([
      'ffmpeg',
      '-y',
      '-i',
      $input,
      '-c:v',
      'libx264',
      '-preset',
      'fast',
      '-movflags',
      '+faststart',
      '-c:a',
      'aac',
      $output
    ]);

    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }
  }

  /**
   * Generate thumbnail JPG dari video
   * 
   * @param string $videoPath Path full file video
   * @param string $thumbnailPath Path full file thumbnail
   * @throws ProcessFailedException
   */
  public function generateThumbnail(string $videoPath, string $thumbnailPath): void
  {
    // pastikan folder thumbnails ada
    @mkdir(dirname($thumbnailPath), 0777, true);

    $process = new Process([
      'ffmpeg',
      '-y',
      '-i',
      $videoPath,
      '-ss',
      '00:00:01',
      '-vframes',
      '1',
      $thumbnailPath
    ]);

    $process->run();

    if (!$process->isSuccessful()) {
      throw new ProcessFailedException($process);
    }
  }

  /**
   * Cek apakah file berformat MP4
   */
  public function isMp4(string $filename): bool
  {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'mp4';
  }
}
