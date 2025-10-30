<?php

namespace App\Services\Master;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class MasterDatatableService
{
 protected array $availableData = [];

 public function __construct()
 {
  $this->availableData = $this->discoverModels();
 }

 /**
  * Discover all model classes inside App\Models\Master
  */
 protected function discoverModels(): array
 {
  $namespace = 'App\\Models\\Master\\';
  $path = app_path('Models/Master');
  $models = [];

  foreach (glob($path . '/*.php') as $file) {
   $className = pathinfo($file, PATHINFO_FILENAME);
   $key = Str::snake($className);
   $models[$key] = $namespace . $className;
  }

  $models['role'] = Role::class;

  return $models;
 }

 /**
  * Get model class by type
  */
 public function getData(string $type): ?string
 {
  $normalized = Str::of($type)->replace('-', '_')->lower();

  return $this->availableData[$normalized] ?? null;
 }
}
