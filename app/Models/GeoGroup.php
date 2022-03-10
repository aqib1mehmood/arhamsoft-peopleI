<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoGroup extends Model
{
    protected $table = 'geo_groups';
    protected $fillable = [
        'label',
        'field_name',
        'parent_id',
        'created_at',
        'updated_at'
    ];
}
