<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    protected $table = 'continents';
    protected $fillable = [
        'continent_id',
        'continent_name',
        'continent_description',
        'created_by',
        'updated_by',
        'created_date', 
        'updated_date',
    ];
    protected $primaryKey = 'continent_id';
}


