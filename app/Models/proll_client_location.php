<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proll_client_location extends Model
{
    protected $table = 'proll_client_location';
    public $timestamps = false;
    public $primaryKey = 'loc_id';

    public static $headers = ["company","branch_name","branch_address","land_line","branch_type","branch_city"];
    protected $fillable = [
        'loc_id','loc_desc','address','branch_type_id','country_id','city_id','cid','status','landline','longitude','latitude',"created_by"];

        public function client_branch_name(){
            
            return $this->hasOne('App\Models\proll_reference_data','id','branch_type_id');
            
        } 
        public function client_country(){
       
            return $this->hasOne('App\Models\country','country_id','country_id');
        }
        public function client_city(){
       
            return $this->hasOne('App\Models\city','id','city_id');
        }

}
