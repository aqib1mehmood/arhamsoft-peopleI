<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceClearance extends Model
{
    protected $dates = ['updated_at', 'created_at'];
    protected $table = 'finance_clearance';
    protected $fillable = ['type','status'];
}
