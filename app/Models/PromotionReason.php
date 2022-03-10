<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PromotionReason extends Model
{
    protected $dates = ['deleted_at'];
    protected $table = "pr_promotion_reasons";
    protected $fillable = ["name"];
    public $timestamps = false;
}
