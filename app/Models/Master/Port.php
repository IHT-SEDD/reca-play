<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const Searchable = ['name', 'port_number', 'is_active'];
    public const Unsearchable = ['id', 'created_at', 'updated_at'];
}
