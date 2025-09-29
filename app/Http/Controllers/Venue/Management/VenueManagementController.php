<?php

namespace App\Http\Controllers\Venue\Management;

use App\Http\Controllers\Controller;
use App\Models\Master\Field;
use App\Models\Master\Venue;
use App\Models\Record\Recording;
use App\Services\CustomDatatable\CustomDatatableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenueManagementController extends Controller
{
    public function index()
    {
        return view('pages.venue.management.index');
    }

    /**
     * Show the form for creating a new resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function data()
    {
        $user = Auth::user();
        $venueId = $user->venue_id;

        $venueName = Venue::where('id', $venueId)->pluck('name');
        $fieldIds = Field::where('venue_id', $venueId)->pluck('id');

        $dataTotalVideo = Recording::whereIn('field_id', $fieldIds)->count();
        $dataTotalVisitor = Recording::whereIn('field_id', $fieldIds)
            ->distinct('user_id')
            ->count('user_id');

        return response()->json([
            'venue_name' => $venueName,
            'total_video' => $dataTotalVideo,
            'total_visitor' => $dataTotalVisitor,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
