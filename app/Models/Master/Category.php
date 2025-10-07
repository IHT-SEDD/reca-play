<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const Searchable = ['name', 'is_active'];
    public const Unsearchable = ['id', 'description', 'created_at', 'updated_at'];
}
