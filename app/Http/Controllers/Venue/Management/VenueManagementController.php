<?php

namespace App\Http\Controllers\Venue\Management;

use App\Http\Controllers\Controller;
use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Models\Record\Recording;
use App\Services\CustomDatatable\CustomDatatableService;
use Illuminate\Support\Facades\URL;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;
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

        $lastActivityData = Recording::with(['user'])->where('field_id', $fieldId);

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
}
