<?php

namespace App\Http\Controllers\Venue;

use App\Http\Controllers\Controller;
use App\Models\Master\Field;
use App\Models\Master\Venue;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.venue.index');
    }

    public function data(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $venues = Venue::query();

        if ($search) {
            $venues->where('name', 'like', "%{$search}%");
        }

        return response()->json($venues->paginate($perPage));
    }

    public function detail($hashedId)
    {
        return view('pages.venue.detail', compact('hashedId'));
    }

    public function dataDetailPage($hashedId)
    {
        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $venueId = $decoded[0];
        $detailVenue = Venue::with(['venueType'])->findOrFail($venueId);
        $totalCourt = Field::where('venue_id', $detailVenue->id)->count();

        if (!$detailVenue) {
            return response()->json([
                'message' => 'Venue not found'
            ], 404);
        }

        return response()->json([
            'detailVenue' => $detailVenue,
            'total_court' => $totalCourt
        ]);
    }

    public function dataField(Request $request, $hashedId)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $decoded  = Hashids::connection('main')->decode($hashedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $venueId = $decoded[0];
        $dataVenue = Venue::findOrFail($venueId);
        $fields = Field::with(['category'])->where('venue_id', $dataVenue->id);

        if ($search) {
            $fields->where('name', 'like', "%{$search}%");
        }

        return response()->json($fields->paginate($perPage));
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
