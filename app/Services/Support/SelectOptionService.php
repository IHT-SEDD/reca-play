<?php

namespace App\Services\Support;

use Illuminate\Database\Eloquent\Builder;

class SelectOptionService
{
  protected int $limit = 100;

  public function getOptions(string $option, ?string $search = null)
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
    ];

    if (!isset($map[$option])) {
      return collect();
    }

    [$model, $filterActive] = $map[$option];

    return $this->buildQuery($model, $filterActive, $search)->get();
  }

  protected function buildQuery(string $model, bool $filterActive, ?string $search = null): Builder
  {
    $query = $model::query()->select('id', 'name as text');

    if ($filterActive) {
      $query->where('is_active', true);
    }

    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->limit($this->limit);
  }
}
