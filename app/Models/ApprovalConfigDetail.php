<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalConfigDetail extends Model
{
    protected $table = "approval_config_detail";
    protected $fillable = ["approval_config_id","group_id","approval_type","branch_id","level_count","min_approval","cid","deleted"];
    public $timestamps = false; 
}
