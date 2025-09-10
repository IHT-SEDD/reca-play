<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $guarded = ['id'];

    protected $with = ['venueType'];

    public const Searchable = ['name', 'code', 'venue_type_id'];
    public const Unsearchable = ['id', 'description', 'address', 'created_at', 'updated_at'];

    public function venueType()
    {
        return $this->belongsTo(VenueType::class);
    }
}
