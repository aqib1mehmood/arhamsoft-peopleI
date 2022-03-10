<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class country extends Model
{
    protected $table = 'countries';

    public function client_continent(){
       
        return $this->hasOne('App\Models\continent','continent_id','continent_id');
    }

}
