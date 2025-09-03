<?php

namespace App\Services\CustomDatatable;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomDatatableService
{
 /**
  * Handle datatable processing for a query
  *
  * @param Builder $baseQuery - Query tanpa filter
  * @param Request $request   - Request instance untuk mengambil parameter search
  * @param array|null $customSearchable - Override manual kolom searchable (opsional)
  * @param \Closure|null $customize - Callback opsional untuk mengubah datatable sebelum make()
  *
  * @return JsonResponse
  */
 public static function make(
  Builder $baseQuery,
  Request $request,
  ?array $customSearchable = null,
  ?\Closure $customize = null
 ): JsonResponse {
  // Model basequery
  $model = $baseQuery->getModel();

  // Decide searchable column
  if (!empty($customSearchable)) {
   $searchable = $customSearchable;
  } elseif (defined(get_class($model) . '::Searchable')) {
   $searchable = $model::Searchable;
  } elseif (method_exists($model, 'getFillable')) {
   $searchable = $model->getFillable();
  } else {
   $searchable = [];
  }

  // Decide unsearchable column
  if (defined(get_class($model) . '::Unsearchable')) {
   $unsearchable = $model::Unsearchable;
   $searchable = array_diff($searchable, $unsearchable);
  }

  // Clone basequery for searching
  $query = clone $baseQuery;

  // Logic searching
  if ($request->filled('search') && !empty($searchable)) {
   $keyword = $request->search;
   $query->where(function ($q) use ($keyword, $searchable) {
    foreach ($searchable as $column) {
     $q->orWhere($column, 'like', "%{$keyword}%");
    }
   });
  }

  // Make datatable
  $datatable = DataTables::of($query)->addIndexColumn();

  // Add index customize
  if ($customize) {
   $datatable = $customize($datatable);
  }

  // Make JSON datatable
  $json = $datatable->make(true)->getData(true);
  $json['recordsTotal'] = $baseQuery->count();

  // Return to JSON
  return response()->json($json);
 }
}
