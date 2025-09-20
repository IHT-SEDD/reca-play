<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Nvr extends Model
{
    protected $guarded = ['id'];

    protected $with = ['field', 'port'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const Searchable = ['field_id', 'code', 'brand', 'type', 'name', 'initial', 'ip_address', 'is_active'];
    public const Unsearchable = ['id', 'description', 'port_id', 'auth_type', 'username', 'password', 'created_at', 'updated_at'];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }

    // 1 nvr has many cameras
    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }
}
