<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\CustomDatatable\CustomDatatableService;
use App\Services\Master\MasterDatatableService;
use App\Services\Master\MasterFormRequestService;
use App\Services\Master\MasterViewService;
use App\Services\Master\MasterDetailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterController extends Controller
{
    protected MasterViewService $masterViewService;
    protected MasterDatatableService $masterDatatableService;
    protected MasterFormRequestService $masterFormRequestService;
    protected MasterDetailService $masterDetailService;

    public function __construct(
        MasterViewService $masterViewService,
        MasterDatatableService $masterDatatableService,
        MasterFormRequestService $masterFormRequestService,
        MasterDetailService $masterDetailService
    ) {
        $this->masterViewService = $masterViewService;
        $this->masterDatatableService = $masterDatatableService;
        $this->masterFormRequestService = $masterFormRequestService;
        $this->masterDetailService = $masterDetailService;
    }

    // View of master
    public function index($type)
    {
        // Return 404 if view doesn't exist
        if (!$this->masterViewService->exists($type)) {
            abort(404, 'Master view not found.');
        }

        // Return view if view is exist and accessible
        $view = $this->masterViewService->getView($type);
        return view($view, [
            'type' => $type
        ]);
    }

    // Data resource of datatable
    public function datatable(Request $request, $type)
    {
        $modelClass = $this->masterDatatableService->getData($type);

        if (!$modelClass || !class_exists($modelClass)) {
            abort(404, 'Master data not found.');
        }

        $query = $modelClass::query();

        if ($request->has('with')) {
            $relations = (array) $request->get('with');
            $query->with($relations);
        }

        return CustomDatatableService::make(
            $query,
            $request,
            null,
            function ($datatable) use ($type) {
                return match ($type) {
                    'field' => $datatable->editColumn('status', fn($item) => $item->status ? 'Active' : 'Inactive'),
                    'role' => $datatable->addColumn('permissions', fn($role) => $role->permissions->pluck('name')->join(', ')),
                    default => $datatable,
                };
            }
        );
    }

    // Insert new data to database
    public function newData(Request $request, $type)
    {
        $validated = $this->masterFormRequestService->getValidatedData($type, $request, 'store');
        $userId = Auth::id();

        // dd($request->all(), $validated);
        try {
            DB::beginTransaction();

            $modelClass = $this->masterDatatableService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $modelClass::create($validated);

            DB::commit();

            Log::channel('master_add_data')->info("Data {$type} saved successfully", [
                'type' => $type,
                'data' => $validated,
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Data {$type} saved successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::channel('master_add_data')->error("Failed to save data {$type}", [
                'type' => $type,
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $type, string $id)
    {

        $data = $this->masterDetailService->getData($type, $id);

        if (!$data) throw new \Exception('Data not found');

        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateData(Request $request, string $type)
    {

        $validated = $this->masterFormRequestService->getValidatedData($type, $request, 'update');
        $userId = Auth::id();
        $id = $request->id;

        // dd($request->all(), $validated);
        try {
            DB::beginTransaction();

            $modelClass = $this->masterDatatableService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $modelClass::whereId($id)->update($validated);

            DB::commit();

            Log::channel('master_update_data')->info("Data {$type} saved successfully", [
                'type' => $type,
                'data' => $validated,
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => "Data {$type} updated successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::channel('master_add_data')->error("Failed to update data {$type}", [
                'type' => $type,
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
