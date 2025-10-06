<?php

namespace App\Services\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class SelectOptionService
{
  protected int $limit = 100;

  public function getOptions(string $option, ?string $search = null, array $with = [])
  {
    $map = [
      'api' => [\App\Models\Master\Api::class, true],
      'camera' => [\App\Models\Master\Camera::class, true],
      'category' => [\App\Models\Master\Category::class, true],
      'field' => [\App\Models\Master\Field::class, false],
      'nvr' => [\App\Models\Master\Nvr::class, true],
      'port' => [\App\Models\Master\Port::class, true],
      'qr-code' => [\App\Models\Master\QrCode::class, true],
      'venue' => [\App\Models\Master\Venue::class, false],
      'venue-type' => [\App\Models\Master\VenueType::class, true],
      'role' => [Role::class, true],
    ];

    if (!isset($map[$option])) {
      return collect();
    }

    [$model, $filterActive] = $map[$option];

    return $this->buildQuery($model, $filterActive, $search, $with)->get();
  }

  // protected function buildQuery(string $model, bool $filterActive, ?string $search = null, array $with = []): Builder
  // {
  //   $query = $model::query()->select('id', 'name as text');

  //   if (!empty($with)) {
  //     $query->with($with);
  //   }

  //   if ($filterActive && Schema::hasColumn((new $model)->getTable(), 'is_active')) {
  //     $query->where('is_active', true);
  //   }

  //   if ($search) {
  //     $query->where('name', 'like', "%{$search}%");
  //   }

  //   return $query->limit($this->limit);
  // }

  protected function buildQuery(string $model, bool $filterActive, ?string $search = null, array $with = []): Builder
  {
    $instance = new $model;
    $table = $instance->getTable();

    $select = ['id', 'name as text'];

    // ✅ tambahkan foreign key jika relasi disebut di $with
    foreach ($with as $relation) {
      $fk = "{$relation}_id";
      if (Schema::hasColumn($table, $fk)) {
        $select[] = $fk;
      }
    }

    $query = $model::query()->select($select);

    if (!empty($with)) {
      $query->with($with);
    }

    if ($filterActive && Schema::hasColumn($table, 'is_active')) {
      $query->where('is_active', true);
    }

    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->limit($this->limit);
  }
}
