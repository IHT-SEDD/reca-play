<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\CustomDatatable\CustomDatatableService;
use App\Services\Master\MasterDatatableService;
use App\Services\Master\MasterFormRequestService;
use App\Services\Master\MasterViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    protected MasterViewService $masterViewService;
    protected MasterDatatableService $masterDatatableService;
    protected MasterFormRequestService $masterFormRequestService;

    public function __construct(
        MasterViewService $masterViewService,
        MasterDatatableService $masterDatatableService,
        MasterFormRequestService $masterFormRequestService
    ) {
        $this->masterViewService = $masterViewService;
        $this->masterDatatableService = $masterDatatableService;
        $this->masterFormRequestService = $masterFormRequestService;
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

        $searchable = match ($type) {
            'field' => ['name', 'description'],
            'role' => ['name', 'guard_name'],
            'category' => ['name'],
            default => ['id'],
        };

        return CustomDatatableService::make(
            $query,
            $request,
            $searchable,
            function ($datatable) use ($type) {
                return match ($type) {
                    'field' => $datatable->editColumn('status', fn($item) => $item->status ? 'Active' : 'Inactive'),
                    'role' => $datatable->addColumn('permissions', fn($role) => $role->permissions->pluck('name')->join(', ')),
                    'category' => $datatable->editColumn('created_at', fn($item) => $item->created_at->format('Y-m-d')),
                    default => $datatable,
                };
            }
        );
    }

    // Insert new data to database
    public function newData(Request $request, $type)
    {
        $validated = $this->masterFormRequestService->getValidatedData($type, $request);

        try {
            DB::beginTransaction();

            $modelClass = $this->masterDatatableService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                throw new \Exception("Model for {$type} not found");
            }

            $modelClass::create($validated);

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => "Data {$type} saved successfully"
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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
