<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Venue extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['hashed_id'];

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

    public function getHashedIdAttribute()
    {
        return Hashids::connection('main')->encode($this->id);
    }
}
