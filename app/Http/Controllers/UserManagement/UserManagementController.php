<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomDatatable\CustomDatatableService;
use App\Services\UserManagement\UserFormRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    protected UserFormRequestService $userFormRequestService;

    public function __construct(UserFormRequestService $userFormRequestService)
    {
        $this->userFormRequestService = $userFormRequestService;
    }

    public function index()
    {
        return view('pages.user-management.index');
    }

    public function usersData(Request $request)
    {
        return CustomDatatableService::make(
            User::query(),
            $request,
            ['name', 'username', 'email'],
            function ($datatable) {
                return $datatable->editColumn('status', fn($user) => 'Active');
            }
        );
    }

    public function addData(Request $request)
    {
        $validated = $this->userFormRequestService->getValidatedData($request);
        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);

            $role = Role::findOrFail($validated['role_id']);
            $user->assignRole($role);

            DB::commit();

            Log::channel('user_add_data')->info("User saved successfully", [
                'data' => $validated,
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "User saved successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::channel('user_add_data')->error("Failed to save user", [
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
