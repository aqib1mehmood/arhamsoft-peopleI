<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AssetClearance extends Model
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at', 'updated_at', 'created_at'];
    protected $table = 'employee_resource_clearance';
    protected $fillable = ['client_id','emp_id','resignation_id','employee_resource_id'];
}
