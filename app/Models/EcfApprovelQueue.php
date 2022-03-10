<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class EcfApprovelQueue extends Model
{
    //
    use SoftDeletes;
    protected $dates = ['deleted_at', 'updated_at', 'created_at'];
    protected $table = 'ecf_approvel_queue';
    protected $fillable = ['role','emp_id','resignation_id','client_id','remarks'];
}


