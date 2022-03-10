<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentManager extends Model
{
    protected $table = 'proll_department_managers';
    protected $fillable = ['id','empid','line_manager','parent_reporting_lm','department_hierarchy_id','reported_sub_department_id','cid','email','client_code','password','status','department_region_id','department','dep_description','e_password','loggedin','empcode','department_group_heirarchy_id'];
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';
    public function proll_employee()
    {
        return $this->hasOne('App\Models\Employee','id','empid');
    }
    function department_heirarchy(){
        return $this->hasone('App\Models\Department','id','department_hierarchy_id');
    }
}
