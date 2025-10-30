<?php

namespace App\Models\Master;

use App\Enums\MasterStatus;
use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => MasterStatus::class,
    ];

    public const Searchable = ['name', 'port_number', 'is_active'];
    public const Unsearchable = ['id', 'created_at', 'updated_at'];
}
