<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Award extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at', 'updated_at', 'created_at'];
    protected $table = 'pr_awards';
    protected $fillable = ['amount','brief_reason','documents'];
    public function employees()
    {
        return $this->belongsTo('App\Models\Employee', 'emp_id', 'id');
    }

    public function awardTypes()
    {
        return $this->belongsTo('App\Models\AwardType', 'award_type', 'id');
    }

    public function documents()
    {
        return $this->morphMany('App\Models\Documents', 'pr_documentable');
    }
}

