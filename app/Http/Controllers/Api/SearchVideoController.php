<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchVideoController extends Controller
{
    public function search(Request $req)
    {
        $req->validate([
            'host' => 'required|string',
            'user' => 'required|string',
            'pass' => 'required|string',
            'channel' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        $host = $req->host;
        $user = $req->user;
        $pass = $req->pass;
        $channel = $req->channel;

        $startTs = strtotime($req->start_time);
        $endTs = strtotime($req->end_time);

        $startISO = gmdate('Y-m-d\TH:i:s\Z', $startTs);
        $endISO = gmdate('Y-m-d\TH:i:s\Z', $endTs);

        $xmlPayload = $this->buildSearchXmlPayload($channel, $startISO, $endISO);

        Log::info("[CAMERA API TEST] XML Payload", [
            'channel' => $channel,
            'payload' => $xmlPayload
        ]);

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
                'auth' => [$user, $pass, 'digest']
            ])
                ->withHeaders(['Content-Type' => 'application/xml'])
                ->withBody($xmlPayload, 'application/xml')
                ->post("https://{$host}/ISAPI/ContentMgmt/search");
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'body' => $response->body()
            ], 400);
        }

        $xml = @simplexml_load_string($response->body());

        if (!$xml || !isset($xml->matchList)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid XML from NVR'
            ], 422);
        }

        $uris = $this->extractUrisFromXml($xml, $startTs, $endTs);

        return response()->json([
            'success' => true,
            'count' => count($uris),
            'requested_start' => $req->start_time,
            'requested_end' => $req->end_time,
            'uris' => $uris
        ]);
    }

    protected function buildSearchXmlPayload(string $channel, string $start, string $end): string
    {
        $searchId = strtoupper(Str::uuid()->toString());

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CMSearchDescription version="1.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <searchID>{$searchId}</searchID>
    <trackIDList>
        <trackID>{$channel}</trackID>
    </trackIDList>
    <timeSpanList>
        <timeSpan>
            <startTime>{$start}</startTime>
            <endTime>{$end}</endTime>
        </timeSpan>
    </timeSpanList>
    <maxResults>40</maxResults>
    <searchResultPosition>0</searchResultPosition>
</CMSearchDescription>
XML;
    }

    private function extractUrisFromXml(\SimpleXMLElement $xml, int $startTs, int $endTs, int $tolerance = 60): array
    {
        $uris = [];

        foreach ($xml->matchList->searchMatchItem as $item) {

            $segStart = strtotime((string)$item->timeSpan->startTime);
            $segEnd   = strtotime((string)$item->timeSpan->endTime);

            // Apply tolerance
            $segStart -= $tolerance;
            $segEnd   += $tolerance;

            if (!($segStart <= $endTs && $segEnd >= $startTs)) {
                continue;
            }

            $uri = (string) $item->mediaSegmentDescriptor->playbackURI;
            if (!$uri) continue;

            // Extract timestamps
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

            // Fix URI timestamp
            $uri = preg_replace('/starttime=\d{8}T\d{6}/', "starttime=" . gmdate('Ymd\THis', $uriStart), $uri);
            $uri = preg_replace('/endtime=\d{8}T\d{6}/', "endtime=" . gmdate('Ymd\THis', $uriEnd), $uri);

            $recordingDuration = $endTs - $startTs;
            $coverageRatio = ($uriEnd - $uriStart) / max($recordingDuration, 1);

            $startDiff = abs($uriStart - $startTs);
            $endDiff   = abs($uriEnd   - $endTs);

            // Format waktu
            $startIso = gmdate('Y-m-d\TH:i:s\Z', $uriStart);
            $endIso   = gmdate('Y-m-d\TH:i:s\Z', $uriEnd);

            $uris[] = [
                'uri' => $uri,

                // timestamp asli
                'start_ts' => $uriStart,
                'end_ts'   => $uriEnd,

                // waktu terformat
                'start' => $startIso,
                'end'   => $endIso,

                'coverageRatio' => $coverageRatio,
                'startDiff' => $startDiff,
                'endDiff' => $endDiff,
                'totalDeviation' => $startDiff + $endDiff,
                'directUse' => $coverageRatio >= 0.80,
            ];
        }

        usort($uris, fn($a, $b) => $a['start_ts'] <=> $b['start_ts']);

        return $uris;
    }
}
