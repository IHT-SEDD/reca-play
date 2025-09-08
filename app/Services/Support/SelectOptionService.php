<?php

namespace App\Services\Support;

use App\Models\Master\Category;
use App\Models\Master\Venue;
use App\Models\Master\VenueType;

class SelectOptionService
{
  public function getOptions(string $option, ?string $search = null)
  {
    switch ($option) {
      case 'venue-type':
        return $this->getVenueTypes($search);
      case 'venue':
        return $this->getVenue($search);
      case 'category':
        return $this->getCategories($search);
      default:
        return [];
    }
  }

  protected function getVenueTypes(?string $search = null)
  {
    $query = VenueType::query()->select('id', 'name as text')->where('is_active', true);

    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->limit(20)->get();
  }

  protected function getVenue(?string $search = null)
  {
    $query = Venue::query()->select('id', 'name as text');

    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->limit(20)->get();
  }

  protected function getCategories(?string $search = null)
  {
    $query = Category::query()->select('id', 'name as text')->where('is_active', true);

    if ($search) {
      $query->where('name', 'like', "%{$search}%");
    }

    return $query->limit(20)->get();
  }
}
