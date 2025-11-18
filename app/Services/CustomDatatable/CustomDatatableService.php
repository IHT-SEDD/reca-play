<?php

namespace App\Services\CustomDatatable;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Custom Datatable Service
 *
 * - Supports custom searchable columns
 * - Can read `Searchable` / `Unsearchable` constants from the model
 * - Falls back to `$fillable` if no constants are defined
 */
class CustomDatatableService
{
 /**
  * Handle datatable processing for a query
  *
  * @param Builder $baseQuery Base query without filters
  * @param Request $request Request instance (for search parameters)
  * @param array|null $customSearchable Optional override for searchable columns
  * @param \Closure|null $customize Optional callback to modify datatable before `make()`
  *
  * @return JsonResponse
  */
 public static function make(
  Builder $baseQuery,
  Request $request,
  ?array $customSearchable = null,
  ?\Closure $customize = null,
  ?string $dateColumn = 'created_at'
 ): JsonResponse {
  // Model basequery
  $model = $baseQuery->getModel();

  // --- Determine searchable columns ---
  if (!empty($customSearchable)) {
   $searchable = $customSearchable;
  } elseif (defined($model::class . '::Searchable')) {
   $searchable = constant($model::class . '::Searchable');
  } elseif (method_exists($model, 'getFillable')) {
   $searchable = $model->getFillable();
  } else {
   $searchable = [];
  }

  // --- Exclude unsearchable columns if defined ---
  if (defined($model::class . '::Unsearchable')) {
   $unsearchable = constant($model::class . '::Unsearchable');
   $searchable   = array_diff($searchable, $unsearchable);
  }

  // Clone basequery for searching
  $query = clone $baseQuery;

  // --- Apply search keyword ---
  if ($request->filled('search') && !empty($searchable)) {
   $keyword = $request->input('search');
   $query->where(function ($q) use ($keyword, $searchable) {
    foreach ($searchable as $column) {
     $q->orWhere($column, 'like', "%{$keyword}%");
    }
   });
  }

  // --- Apply date filter ---
  if ($request->filled('date_start') && $request->filled('date_end')) {
   $start = $request->date_start . " 00:00:00";
   $end = $request->date_end . " 23:59:59";

   $query->whereBetween($dateColumn, [$start, $end]);
  }

  // --- Build datatable ---
  $datatable = DataTables::of($query)->addIndexColumn();

  // --- Apply custom callback if provided ---
  if ($customize) {
   $datatable = $customize($datatable);
  }

  // --- Generate JSON output ---
  $json = $datatable->make(true)->getData(true);

  // Override recordsTotal with unfiltered count
  $json['recordsTotal'] = $baseQuery->count();

  // --- Return JSON response ---
  return response()->json($json);
 }
}
