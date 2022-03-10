<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class proll_client_assist extends Model
{
    protected $table = 'proll_client_assist';
    public $timestamps = false;
    protected $fillable = [
        'user_name','emp_id','cid','empcode','name_salute','city','auth_repre_name','companyname','role_id','off_address','cell_number','access_role','pass_word','e_pass_word','country','status'];
   
    protected $visible = [
        'user_name','id','cid','imp_id','emp_id','name_salute','city','empcode','auth_repre_name','companyname','off_address','cell_number','access_role','country','status','pass_word','e_pass_word'];

    public function access_role_details(){
        return $this->hasone('App\Models\Role','id','access_role');
    }
    public function country_details(){
        return $this->hasone('App\Models\country','country_id','country')->select('country_id',"country","country_code");
    }
    public function status_detail(){
        return $this->hasone('App\Models\proll_reference_data','reference_key','status')->wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Job_Status');
        })->select('ref_id',"description","id");
    }
    public function name_salutation_detail(){
        return $this->hasone('App\Models\proll_reference_data','reference_key','name_salute')->wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','name_salutation');
        })->select('ref_id',"description","id");
    }

}
