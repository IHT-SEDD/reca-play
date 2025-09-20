<?php

namespace App\Http\Controllers\Venue;

use App\Http\Controllers\Controller;
use App\Models\Master\Field;
use App\Models\Master\Venue;
use Illuminate\Http\Request;

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

    public function detail()
    {
        return view('pages.venue.detail');
    }

    public function dataDetailPage(string $code)
    {
        $detailVenue = Venue::where('code', $code)->first();
        $dataField = Field::where('venue_id', $detailVenue->id)->get();

        if (!$detailVenue) {
            return response()->json([
                'message' => 'Venue not found'
            ], 404);
        }

        return response()->json([
            'detailVenue' => $detailVenue,
            'dataField' => $dataField,
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
