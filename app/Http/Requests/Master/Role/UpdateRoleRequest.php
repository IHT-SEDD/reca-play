<?php

namespace App\Http\Requests\Master\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends StoreRoleRequest
{
       // Tidak perlu override rules, karena validasi sama dengan parent nya
}
