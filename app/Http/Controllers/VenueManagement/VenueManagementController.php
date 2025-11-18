<?php

namespace App\Http\Controllers\VenueManagement;

use App\Http\Controllers\Controller;

use App\Enums\RecordingLogStatus;
use App\Enums\RecordingStatus;
use App\Enums\StreamingLogStatus;
use App\Enums\StreamingStatus;
use App\Enums\SessionCodeStatus;

use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Models\Record\Recording;
use App\Models\Record\RecordingLog;
use App\Models\Session\SessionCode;
use App\Models\Stream\Streaming;
use App\Models\Stream\StreamingLog;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\CustomDatatable\CustomDatatableService;
use Vinkla\Hashids\Facades\Hashids;

class VenueManagementController extends Controller
{
    // ===============================
    // Main View
    // ===============================
    public function index()
    {
        return view('pages.venue.management.index');
    }

    // ===============================
    // Datatable Field List
    // ===============================
    public function fieldList(Request $request)
    {
        $user = Auth::user();
        $venueId = $user->venue_id;

        $fieldList = Field::with(['category'])->where('venue_id', $venueId);

        return CustomDatatableService::make(
            $fieldList,
            $request,
            null,
            null,
        );
    }

    // ===============================
    // Data Statistic Venue
    // ===============================
    public function data()
    {
        $user = Auth::user();
        $venueId = $user->venue_id;

        $venueName = Venue::where('id', $venueId)->pluck('name');
        $fieldIds = Field::where('venue_id', $venueId)->pluck('id');

        $dataTotalVideo = Recording::with(['user', 'field', 'camera'])->whereIn('field_id', $fieldIds)->count();
        $dataTotalVisitor = Recording::with(['user', 'field', 'camera'])->whereIn('field_id', $fieldIds)
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'venue_name' => $venueName,
            'total_video' => $dataTotalVideo,
            'total_visitor' => $dataTotalVisitor,
        ]);
    }

    // ===============================
    // Detail Field View
    // ===============================
    public function detailFieldPage($hashedId)
    {
        return view('pages.venue.management.detail-field', compact('hashedId'));
    }

    // ===============================
    // Data Statistic Field Detail
    // ===============================
    public function detailFieldData($hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $fieldId = $decoded[0];

        $dataTotalVideo = Recording::with(['user', 'field', 'camera'])->where('field_id', $fieldId)->count();
        $dataTotalVisitor = Recording::with(['user', 'field', 'camera'])->where('field_id', $fieldId)
            ->distinct('user_id')
            ->count('user_id');

        $meanDuration = Recording::with(['user', 'field', 'camera'])->where('field_id', $fieldId)->avg('duration');
        $meanDuration = round($meanDuration, 2);

        $peakHour = Recording::with(['user', 'field', 'camera'])->selectRaw('HOUR(start_time) as hour, COUNT(*) as total')
            ->where('field_id', $fieldId)
            ->groupBy('hour')
            ->orderByDesc('total')
            ->first();

        $field = Field::with('category')->findOrFail($fieldId);

        return response()->json([
            'dataTotalVideo' => $dataTotalVideo,
            'dataTotalVisitor' => $dataTotalVisitor,
            'dataMeanDuration' => $meanDuration,
            'dataPeakHour' => $peakHour ? $peakHour->hour : null,
            'field' => $field,
        ]);
    }

    // ===============================
    // Data Last Activity Field
    // ===============================
    public function lastActivity(Request $request, $hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $fieldId = $decoded[0];

        $lastActivityData = Recording::with(['user'])
            ->where('field_id', $fieldId)
            ->orderByDesc('created_at')
            ->limit(10);

        return CustomDatatableService::make(
            $lastActivityData,
            $request,
            null,
            null,
        );
    }

    // ===============================
    // Datatable Access Code
    // ===============================
    public function accessCode(Request $request, $hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $fieldId = $decoded[0];

        $lastActivityData = SessionCode::with(['user', 'qrCode', 'venue', 'field', 'recording', 'logs', 'generatedBy'])
            ->where('field_id', $fieldId)
            ->orderByDesc('created_at');

        return CustomDatatableService::make(
            $lastActivityData,
            $request,
            null,
            null,
            'start_time'
        );
    }

    // ===============================
    // Update Status Field
    // ===============================
    public function updateStatusActive($hashedId)
    {
        try {
            DB::beginTransaction();
            $decoded  = Hashids::connection('main')->decode($hashedId);
            if (empty($decoded)) {
                return response()->json(['error' => 'Invalid ID'], 400);
            }

            $fieldId = $decoded[0];

            $field = Field::select('id', 'is_active')->findOrFail($fieldId);
            $newStatus = $field->is_active == 1 ? 0 : 1;

            Field::where('id', $fieldId)->update([
                'is_active' => $newStatus,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update status field successfully',
                'field' => [
                    'id' => $fieldId,
                    'is_active' => $newStatus
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to update status',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // ===============================
    // Add New Access Code
    // ===============================
    public function newAccessCode(Request $request, $hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $fieldId = $decoded[0];
        $field = Field::select('id', 'venue_id', 'name')->findOrFail($fieldId);
        $venue = $field->venue()->select('id', 'name')->first();

        $validated = $this->validateAccessCode($request);

        // dd($validated);
        try {
            DB::beginTransaction();

            $timeData = $this->calculateDuration(
                $validated['start_time'],
                $validated['end_time']
            );

            do {
                $generatedCode = $this->generateAccessCodeString($venue->name ?? '', $field->name ?? '', $timeData['duration']);
            } while (SessionCode::where('generated_code', $generatedCode)->exists());

            $sessionCode = SessionCode::create([
                'venue_id' => $field->venue_id,
                'field_id' => $fieldId,
                'generate_by_user_id' => Auth::id(),
                'type' => $validated['type'],
                'status' => SessionCodeStatus::Active,
                'generated_code' => $generatedCode,
                'name' => $validated['name'],
                'start_time' => $timeData['start_time'],
                'end_time' => $timeData['end_time'],
                'duration' => $timeData['duration'],
                'expired_at' => $timeData['expired_at'],
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Session code saved successfully',
                'data' => [
                    'generated_code' => $sessionCode->generated_code,
                    'duration' => $timeData['duration'],
                    'expired_at' => $timeData['expired_at'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // (private) Validate Inputs Access Code Form
    // ===============================
    private function validateAccessCode(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255', 'min:3'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ], [
            'name.max' => 'Name maximum is 255 characters.',
            'name.min' => 'Name minimum is 3 characters.',
            'type.required' => 'Type cannot be empty.',
            'start_time.required' => 'Start time cannot be empty.',
            'end_time.required' => 'End time cannot be empty.',
        ]);
    }

    // ===============================
    // (private) Calculate Duration
    // ===============================
    private function calculateDuration(string $startTime, string $endTime): array
    {
        $start = Carbon::hasFormat($startTime, 'Y-m-d H:i')
            ? Carbon::parse($startTime)
            : Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $startTime);

        $end = Carbon::hasFormat($endTime, 'Y-m-d H:i')
            ? Carbon::parse($endTime)
            : Carbon::parse(Carbon::today()->format('Y-m-d') . ' ' . $endTime);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $duration = (int)$start->diffInMinutes($end);

        return [
            'duration' => $duration,
            'expired_at' => $end->toDateTimeString(),
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ];
    }

    // ===============================
    // (private) Generate Access Code
    // ===============================
    private function generateAccessCodeString(string $venueName, string $fieldName, int $duration): string
    {
        $venueInitial = $this->getInitial($venueName);
        $fieldInitial = $this->getInitial($fieldName);

        $letters = '';
        for ($i = 0; $i < 2; $i++) {
            $letters .= chr(random_int(65, 90));
        }

        $number = random_int(0, 9);
        $mixed = str_shuffle($letters . $number);

        $durationPart = str_pad($duration, 2, '0', STR_PAD_LEFT);

        return "{$venueInitial}{$fieldInitial}{$durationPart}{$mixed}";
    }

    // ===============================
    // (private) Get Initial Field & Venue
    // ===============================
    private function getInitial(string $name): string
    {
        $cleanName = strtoupper(preg_replace('/[^A-Z ]/i', '', $name));
        $words = preg_split('/\s+/', trim($cleanName));

        if (count($words) > 1) {
            $initial = substr($words[0], 0, 1) . substr($words[1], 0, 1);
        } else {
            $initial = substr($cleanName, 0, 2);
        }

        return str_pad($initial, 2, 'X');
    }

    // ===============================
    // Start Recording
    // ===============================
    public function startRecordingOrStreaming(Request $request, $hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $fieldId = $decoded[0];
        $field = Field::select('id', 'venue_id', 'name')->findOrFail($fieldId);
        $venue = $field->venue()->select('id', 'name')->first();

        $sessionCodeId = $request->input('sessionCodeId');

        try {
            DB::beginTransaction();

            $sessionCode = SessionCode::findOrFail($sessionCodeId);
            $message = '';

            if ($sessionCode->type === 'record') {
                $cameraService = app(\App\Services\Camera\CameraControlService::class);
                $cameraService->initialize($fieldId);

                $record = Recording::create([
                    'field_id' => $fieldId,
                    'session_code_id' => $sessionCode->id,
                    'video_name' => $sessionCode->name,
                    'duration' => $sessionCode->duration,
                    'start_time' => $sessionCode->start_time,
                    'end_time' => $sessionCode->end_time,
                    'status' => RecordingStatus::Recording,
                ]);

                RecordingLog::create([
                    'recording_id' => $record->id,
                    'status' => RecordingLogStatus::RecordStart,
                ]);

                if ($cameraService->startRecording()) {
                    Log::channel('camera-record')->info('[RECORD] Recording started', [
                        'recording_id' => $record->id,
                        'field_id' => $fieldId,
                        'timestamp' => Carbon::now(),
                    ]);
                } else {
                    throw new \Exception("Failed to start recording");
                }

                $message = 'Record successfully started!';
            } elseif ($sessionCode->type === 'stream') {
                $cameraService = app(\App\Services\Camera\CameraControlService::class);
                $cameraService->initialize($fieldId);

                $stream = Streaming::create([
                    'field_id' => $fieldId,
                    'session_code_id' => $sessionCode->id,
                    'video_name' => $sessionCode->name,
                    'duration' => $sessionCode->duration,
                    'start_time' => $sessionCode->start_time,
                    'end_time' => $sessionCode->end_time,
                    'status' => StreamingStatus::Streaming,
                ]);

                StreamingLog::create([
                    'streaming_id' => $stream->id,
                    'status' => StreamingLogStatus::StreamStart,
                ]);

                if ($cameraService->startRecording()) {
                    Log::channel('camera-stream')->info('[STREAM] Streaming & Recording started', [
                        'streaming_id' => $stream->id,
                        'field_id' => $fieldId,
                        'timestamp' => Carbon::now(),
                    ]);
                } else {
                    throw new \Exception("Failed to start streaming");
                }

                $message = 'Stream successfully started!';
            }

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
