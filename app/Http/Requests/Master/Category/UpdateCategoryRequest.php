<?php

namespace App\Http\Requests\Master\Category;

use App\Http\Requests\Master\Venue\StoreVenueTypeRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends StoreVenueTypeRequest
{
    // Tidak perlu override rules, karena validasi sama dengan parent nya
}
