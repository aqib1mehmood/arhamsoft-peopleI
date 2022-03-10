<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'id','name', 'role','cid','img','description'];
    public function proll_client_assist()
    {
        return $this->belongsTo('App\Models\proll_client_assist','id');
    }
    public $timestamps = false;
}
