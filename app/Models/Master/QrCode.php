<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $with = ['venue', 'field'];

    public const Searchable = ['name', 'code', 'field_id', 'venue_id', 'type', 'is_active'];
    public const Unsearchable = ['id', 'description', 'qr_path', 'created_at', 'updated_at'];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
