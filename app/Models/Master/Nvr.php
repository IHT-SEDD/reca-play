<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Nvr extends Model
{
    protected $guarded = ['id'];

    protected $with = ['camera'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const Searchable = ['code', 'brand', 'type', 'name', 'initial', 'ip_address', 'camera_id', 'is_active'];
    public const Unsearchable = ['id', 'description', 'created_at', 'updated_at'];

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }
}
