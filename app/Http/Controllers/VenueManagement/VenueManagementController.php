<?php

namespace App\Http\Controllers\VenueManagement;

use App\Http\Controllers\Controller;
use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Models\Record\Recording;
use App\Models\Session\SessionCode;
use App\Services\CustomDatatable\CustomDatatableService;
use Illuminate\Support\Facades\URL;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VenueManagementController extends Controller
{
    public function index()
    {
        return view('pages.venue.management.index');
    }

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

    public function detailFieldPage($hashedId)
    {
        return view('pages.venue.management.detail-field', compact('hashedId'));
    }

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

    public function generateAccessCode($hashedId)
    {
        try {
            DB::beginTransaction();
            $decoded  = Hashids::connection('main')->decode($hashedId);
            if (empty($decoded)) {
                return response()->json(['error' => 'Invalid ID'], 400);
            }

            $fieldId = $decoded[0];
            $field = Field::select('id', 'venue_id', 'name')->findOrFail($fieldId);
            $venue = $field->venue()->select('id', 'name')->first();

            $duration = request()->input('duration');
            if (!$duration || !is_numeric($duration)) {
                return response()->json(['error' => 'Invalid duration value'], 422);
            }

            $durationInMinutes = (int)$duration * 60;

            $generatedCode = $this->generateCode($venue->name ?? '', $field->name ?? '', $durationInMinutes);

            $sessionCode = SessionCode::create([
                'field_id' => $fieldId,
                'venue_id' => $field->venue_id,
                'generate_by_user_id' => Auth::id(),
                'status' => 'active',
                'generated_code' => $generatedCode,
                'duration' => $durationInMinutes,
                'expired_at' => Carbon::now()->addDay(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Access code generated successfully',
                'generated_code' => $sessionCode->generated_code,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to generate access code',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    private function generateCode(string $venueName, string $fieldName, string $durationInMinutes): string
    {
        $venueInitial = $this->getInitial($venueName);
        $fieldInitial = $this->getInitial($fieldName);
        $durationPart = str_pad($durationInMinutes, 3, '0', STR_PAD_LEFT);
        $randomNumber = str_pad(random_int(1, 99999), 4, '0', STR_PAD_LEFT);

        return "{$venueInitial}{$fieldInitial}{$randomNumber}";
    }

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
}
