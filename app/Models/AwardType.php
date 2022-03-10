<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwardType extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "pr_award_types";
    protected $fillable = ["name"];
    public $timestamps = false;

    public function awardTypes()
    {
        return $this->hasMany('App\Models\Award', 'emp_id', 'id');
    }
}
