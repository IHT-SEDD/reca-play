<?php

namespace App\Services\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class MasterDetailService
{
 protected array $masterData = [];

 public function __construct()
 {
  $this->masterData = $this->discoverModels();
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
 public function getData(string $type, string|int $id): ?Model
 {
  $normalized = (string) Str::of($type)->replace('-', '_')->lower();

  if (! isset($this->masterData[$normalized])) {
   return null;
  }

  $modelClass = $this->masterData[$normalized];

  if (! is_subclass_of($modelClass, Model::class)) {
   return null;
  }

  return $modelClass::find($id);
 }
}
