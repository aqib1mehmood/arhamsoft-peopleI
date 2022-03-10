<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class city extends Model
{
    protected $table = 'cities';
    public function client_state(){
        return $this->hasOne('App\Models\state','id','state_id');
    }
}
