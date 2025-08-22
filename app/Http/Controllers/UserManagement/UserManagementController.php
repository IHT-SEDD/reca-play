<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomDatatable\CustomDatatableService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
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
}
