<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class FinalSettlement extends Model
{
    //
    protected $dates = ['updated_at', 'created_at'];
    protected $table = 'final_settlements';
    protected $fillable = ['payable_amount'];
}
