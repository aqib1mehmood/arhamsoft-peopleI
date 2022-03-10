<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department_hierarchy';
    protected $fillable = [
        'department_name','type', 'dep_description','reporting_department_id','dep_code','department_hierarchy_id','country_id','cid', 'status','department_group_heirarchy_id','iseditable'
    ];
    public function line_manager(){
        return $this->hasOne('App\Models\DepartmentManager','department_hierarchy_id','id')->where('status','=',1);

    }
    public function employeeDetails(){
        return $this->belongsTo('App\Models\Employee','id','department_id');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee','id');
    }
    public function department_group_heirarchy(){
        return $this->hasOne('App\Models\department_group_heirarchy','id','department_group_heirarchy_id');
    }
    public function parent_department(){
        return $this->hasOne('App\Models\Department','id','reporting_department_id')->where('type','=','department');
    }
    public function country(){
        return $this->hasOne('App\Models\country','country_id','country_id');
    }

}
