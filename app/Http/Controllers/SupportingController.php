<?php

namespace App\Http\Controllers;

use App\Services\Support\SelectOptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportingController extends Controller
{
    protected $selectOptionService;

    public function __construct(SelectOptionService $selectOptionService)
    {
        $this->selectOptionService = $selectOptionService;
    }

    public function selectOptions($option, Request $request)
    {
        $results = $this->selectOptionService->getOptions($option, $request->get('q'));
        return response()->json($results);
    }
}
