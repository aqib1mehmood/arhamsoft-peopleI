<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
   protected $table = 'proll_client_designation';
   protected $fillable = [
      'designation_id','designation_name','designation_description', 'country_id', 'cid','status'
   ];
   protected $primaryKey = 'designation_id';
   public $timestamps = false;
   public function employeeDetails(){
      return $this->belongsTo('App\Models\Employee','designation_id','designation');
   }
   public function employee(){
      return $this->belongsTo('App\Models\Employee','designation_id');
   }
   public function country(){    
      return $this->hasOne('App\Models\country','country_id','country_id');
   }

}
