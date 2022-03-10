<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class InterviewQuestion extends Model
{
    //
    protected $dates = ['updated_at', 'created_at'];
    protected $table = 'em_interview_questions';
    protected $fillable = ['question_no','rating','client_id','resignation_id'];
}
