<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use SoftDeletes;
    protected $table = 'pr_promotions';
    protected $dates = ['deleted_at', 'updated_at', 'created_at'];
    protected $fillable = ['amount', 'brief_reason'];

    public function documents()
    {
        return $this->morphMany('App\Models\Documents', 'pr_documentable');
    }

    // public function getCreatedAtAttribute($date)
    // {

    //     return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y');
    // }

    // public function getUpdatedAtAttribute($date)
    // {
    //     return Carbon::createFromFormat('Y-m-dH:i:s', $date)->format('Y-m-d');
    // }
}
