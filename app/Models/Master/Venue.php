<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $guarded = ['id'];

    protected $with = ['venueType', 'fields'];

    public const Searchable = ['name', 'code', 'venue_type_id'];
    public const Unsearchable = ['id', 'description', 'address', 'logo_path', 'logo_filename', 'created_at', 'updated_at'];

    public function venueType()
    {
        return $this->belongsTo(VenueType::class);
    }

    public function fields()
    {
        return $this->hasMany(Field::class);
    }
}
