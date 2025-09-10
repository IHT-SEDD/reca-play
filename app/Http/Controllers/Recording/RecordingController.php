<?php

namespace App\Http\Controllers\Recording;

use App\Http\Controllers\Controller;

class RecordingController extends Controller
{
    public function index()
    {
        return view('pages.recording.index');
    }
}
