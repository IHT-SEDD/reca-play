<?php

namespace App\Http\Controllers\MyProfile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Support\ResponseHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyProfileController extends Controller
{
    // ============================================================
    // Init services
    // ============================================================
    protected ResponseHelperService $responseHelperService;

    public function __construct(
        ResponseHelperService $responseHelperService
    ) {
        $this->responseHelperService = $responseHelperService;
    }

    // ============================================================
    // Main View
    // ============================================================
    public function index()
    {
        return view('pages.my-profile.index');
    }

    // ============================================================
    // Retrieve User Data
    // ============================================================
    public function userData()
    {
        try {
            $user = User::with(['venue', 'role'])->where('id', Auth::id())->first();

            return $this->responseHelperService->successResponse(
                'User data retrieved successfully',
                encryptData($user)
            );
        } catch (\Throwable $th) {
            return $this->responseHelperService->errorResponse(
                encryptData($th->getMessage()),
            );
        }
    }
}
