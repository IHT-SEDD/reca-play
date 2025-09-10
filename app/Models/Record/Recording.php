<?php

namespace App\Models\Record;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $guarded = ['id'];

    public const Searchable = ['user_id', 'field_id', 'camera_id', 'video_name', 'start_time', 'end_time'];
    public const Unsearchable = ['id', 'duration', 'video_path', 'video_filename', 'video_size', 'created_at', 'updated_at'];
}
