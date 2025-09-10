<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const Searchable = ['name', 'url', 'is_active'];
    public const Unsearchable = ['id', 'username', 'password', 'created_at', 'updated_at'];
}
