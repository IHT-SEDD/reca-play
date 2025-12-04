<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Log;

class SupportUtilitiesService
{

 public static function buildTrimCommand(
  string $inputFile,
  string $outputFile,
  int $startSec,
  int $duration,
  bool $forceEncode
 ): array {
  return $forceEncode
   ? [
    'ffmpeg',
    '-y',
    '-ss',
    (string)$startSec,
    '-i',
    $inputFile,
    '-t',
    (string)$duration,
    '-c:v',
    'libx264',
    '-preset',
    'fast',
    '-crf',
    '23',
    '-c:a',
    'aac',
    '-b:a',
    '128k',
    '-movflags',
    '+faststart',
    $outputFile
   ]
   : [
    'ffmpeg',
    '-y',
    '-i',
    $inputFile,
    '-ss',
    (string)$startSec,
    '-t',
    (string)$duration,
    '-c',
    'copy',
    '-movflags',
    '+faststart',
    $outputFile
   ];
 }

 public static function extractUrisFromXml(
  \SimpleXMLElement $xml,
  int $startTs,
  int $endTs,
  int $tolerance = 60
 ): array {

  $uris = [];

  foreach ($xml->matchList->searchMatchItem as $item) {

   $segStart = strtotime((string)$item->timeSpan->startTime) - $tolerance;
   $segEnd   = strtotime((string)$item->timeSpan->endTime)   + $tolerance;

   if (!($segStart <= $endTs && $segEnd >= $startTs)) {
    continue;
   }

   $uri = (string)$item->mediaSegmentDescriptor->playbackURI;
   if (!$uri) continue;

   preg_match('/starttime=(\d{8}T\d{6})Z?/', $uri, $s);
   preg_match('/endtime=(\d{8}T\d{6})Z?/', $uri, $e);

   if (!isset($s[1], $e[1])) continue;

   $uriStart = max(
    \DateTime::createFromFormat('Ymd\THis', $s[1], new \DateTimeZone('UTC'))->getTimestamp(),
    $startTs
   );

   $uriEnd = min(
    \DateTime::createFromFormat('Ymd\THis', $e[1], new \DateTimeZone('UTC'))->getTimestamp(),
    $endTs
   );

   // Replace start & end time inside the URI
   $uri = preg_replace('/starttime=\d{8}T\d{6}/', "starttime=" . gmdate('Ymd\THis', $uriStart), $uri);
   $uri = preg_replace('/endtime=\d{8}T\d{6}/',   "endtime=" . gmdate('Ymd\THis', $uriEnd),   $uri);

   $fullDuration = $endTs - $startTs;
   $uriDuration  = $uriEnd - $uriStart;
   $ratio = $uriDuration / max($fullDuration, 1);

   $uris[] = [
    'uri' => $uri,
    'start' => $uriStart,
    'end' => $uriEnd,
    'coverageRatio' => $ratio,
    'startDiff' => abs($uriStart - $startTs),
    'endDiff' => abs($uriEnd - $endTs),
    'totalDeviation' => abs($uriStart - $startTs) + abs($uriEnd - $endTs),
    'directUse' => $ratio >= 0.8,
   ];
  }

  usort($uris, fn($a, $b) => $a['start'] <=> $b['start']);

  // Best direct match
  $direct = collect($uris)
   ->filter(fn($u) => $u['coverageRatio'] >= 0.8)
   ->sortBy([['totalDeviation', 'asc'], ['coverageRatio', 'desc']]);

  if ($direct->isNotEmpty()) {
   return [$direct->first()];
  }

  // Fallback
  return $uris;
 }
}
