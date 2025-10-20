<?php

namespace App\Services\Master;

use Illuminate\Database\Eloquent\Model;

class MasterDetailService
{
 /**
  * Mapping type to model class
  */
 protected array $masterData = [
  'field' => 'App\Models\Master\Field',
  'role' => 'Spatie\Permission\Models\Role',
  'category' => 'App\Models\Master\Category',
  'venue' => 'App\Models\Master\Venue',
  'venue-type' => 'App\Models\Master\VenueType',
  'camera' => 'App\Models\Master\Camera',
  'nvr' => 'App\Models\Master\Nvr',
  'qr_code' => 'App\Models\Master\QrCode',
  'port' => 'App\Models\Master\Port',
  'api' => 'App\Models\Master\Api',
 ];

 /**
  * Get model class by type
  */
 public function getData(string $type, string | int $id): ?Model
 {
  return $this->masterData[$type]::find($id) ?? null;
 }
}
