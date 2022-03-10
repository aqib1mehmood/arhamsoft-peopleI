<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class state extends Model
{
    protected $table = 'states';
    public function client_country(){
        return $this->hasOne('App\Models\country','country_id','country_id');
    }
}
