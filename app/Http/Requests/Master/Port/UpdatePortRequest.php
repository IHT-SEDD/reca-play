<?php

namespace App\Http\Requests\Master\Port;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortRequest extends StorePortRequest
{
        // Tidak perlu override rules, karena validasi sama dengan parent nya
}
