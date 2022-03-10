<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class AppraisalType extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "pr_appraisal_types";
    protected $fillable = ["name"];
    public $timestamps = false;
}
