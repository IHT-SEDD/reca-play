<?php

namespace App\Services\Support;

class GetModelService
{
 /**
  * Mapping type to model class
  */
 protected array $availableData = [
  'record' => 'App\Models\Record\Recording',
  'record-log' => 'App\Models\Record\RecordingLog',
 ];

 /**
  * Get model class by type
  */
 public function getData(string $type): ?string
 {
  return $this->availableData[$type] ?? null;
 }
}
