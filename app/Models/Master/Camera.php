<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $with = ['field'];

    public const Searchable = ['code', 'brand', 'type', 'name', 'initial', 'ip_address', 'field_id', 'is_active'];
    public const Unsearchable = ['id', 'description', 'channel', 'created_at', 'updated_at'];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
