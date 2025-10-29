<?php

namespace App\Http\Requests\Master\Venue;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueTypeRequest extends StoreVenueTypeRequest
{
    // Tidak perlu override rules, karena validasi sama dengan parent nya
}
