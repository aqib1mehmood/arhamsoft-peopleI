<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proll_reference_data_code extends Model
{
    protected $table = 'proll_reference_data_code';
    protected $fillable = [
        'reference_code','ref_id'];
    
    public $timestamps = false;
    public function proll_reference_data(){
        return $this->belongsTo('App\Models\proll_reference_data','ref_id');
    }
}
