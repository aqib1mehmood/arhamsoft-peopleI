<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class InterviewQuestionResult extends Model
{
    //
    protected $dates = ['updated_at', 'created_at'];
    protected $table = 'em_interview_result';
    protected $fillable = ['rating_key','comment','client_id','resignation_id'];
}
