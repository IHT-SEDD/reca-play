<?php

namespace App\Services\Master;

class MasterDatatableService
{
 /**
  * Mapping type to model class
  */
 protected array $availableData = [
  'field' => 'App\Models\Master\Field',
  'role' => 'Spatie\Permission\Models\Role',
  'category' => 'App\Models\Master\Category',
 ];

 /**
  * Get model class by type
  */
 public function getData(string $type): ?string
 {
  return $this->availableData[$type] ?? null;
 }
}
