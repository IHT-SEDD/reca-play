<?php

namespace App\Services\Master;

class MasterDatatableService
{
 // Available master data is accessible
 protected array $availableData = [
  'field' => 'App\Models\Master\Field',
  'role' => 'Spatie\Permission\Models\Role',
  'category' => 'App\Models\Master\Category',
 ];

 // Get data based on $type
 public function getData(string $type): ?string
 {
  return $this->availableData[$type] ?? null;
 }
}
