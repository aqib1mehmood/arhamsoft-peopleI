<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proll_reference_data extends Model
{
    protected $table = 'proll_reference_data';
    protected $fillable = [
        'ref_id','reference_key', 'description','id','status','cid','created_by'];
    public $timestamps = false;
    public function proll_reference_data_code(){
        return $this->hasone('App\Models\proll_reference_data_code','ref_id','ref_id');
    }
}
