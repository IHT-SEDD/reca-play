<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = ['id'];

    protected $with = ['category', 'venue'];

    public const Searchable = ['name', 'initial', 'code', 'category_id', 'venue_id'];
    public const Unsearchable = ['id', 'description', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
