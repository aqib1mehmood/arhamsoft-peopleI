<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalConfig extends Model
{
    protected $table = "approval_config";
    protected $fillable = ["role_id","portal_id","configuration_type","cid","status","approval_type","module_id","level_count","reporting_column_sequence"];
    public $timestamps = false; 
}
