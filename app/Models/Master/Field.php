<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = ['id'];

    // protected $with = ['category', 'venue'];

    public const Searchable = ['name', 'initial', 'code', 'category_id', 'venue_id'];
    public const Unsearchable = ['id', 'description', 'pict_path', 'pict_filename', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    // 1 field for 1 nvr
    public function nvr()
    {
        return $this->hasOne(Nvr::class);
    }

    // 1 field has many cameras
    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }
}
