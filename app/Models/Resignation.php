<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Resignation extends Model
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at', 'updated_at', 'created_at'];
    protected $table = 'em_resignations';
    protected $fillable = ['separation_reason','notice_period','last_working_day','res_document'];
}
