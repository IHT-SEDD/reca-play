<?php

namespace App\Services\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MasterStorageService
{
 /**
  * Mapping type to model class
  */
 protected array $masterData = [
  'field' => [
    'column' => 'pic_path',
    'storage' => 'field_pictures'
  ],
  'venue' => [
    'column' => 'logo_path',
    'storage' => 'venue_logos'
  ],
  'qr_code' => [
    'column' => 'qr_path',
    'storage' => 'qr_codes'
  ],
 ];

 /**
  * Get model class by type
  */
 public function getData(string $type, Model $data): ?String
 {
   $masterData = $this->masterData[$type] ?? null;

   if(!$masterData) return false;

     if ($data->{$masterData['column']} && Storage::disk('public')->exists(str_replace('storage/', '', $data->{$masterData['column']}))) {
        Storage::disk('public')->delete(str_replace('storage/', '', $data->{$masterData['column']}));
    }

     return true;
 }
}
