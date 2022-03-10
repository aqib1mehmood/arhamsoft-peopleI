<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\proll_reference_data;
class proll_client extends Model
{
    protected $table = 'proll_client';
    public $timestamps = false;
    public static $headers = ["group","company","company_address","company_postal_code","company_city","company_country","business_type"
    ,"legal_status","registration_number","sales_tax_number","fiscal_year_start","fiscal_year_end","auth_repre_name"
    ,"auth_repre_empcode","auth_repre_designation","auth_repre_cell_no","company_off_phone","company_url","company_email","company_alternative_email","login_username","company_logo"];
    protected $fillable = [
        'companyname','off_address','group_id','user_name','auth_repre_name','empcode','country','designation','off_phone','cell_number','company_url','company_email','fiscal_year_start','fiscal_year_end','sales_tax_number','registration_number','business_type_id','legal_status_id','logo','city','city_id','alt_email','status'];
    public function client_country(){
        return $this->hasOne('App\Models\country','country_id','country');
    }
    public static function client_business_type_id($name){
        return proll_reference_data::where('description','=',$name)->first();
    }
    public function client_business_type(){
        return $this->hasOne('App\Models\proll_reference_data','id','business_type_id');
    }
    public function client_legal_status(){
        return $this->hasOne('App\Models\proll_reference_data','id','legal_status_id');
        
    }
    
}
