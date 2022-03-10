<?php

namespace App\Http\Controllers\api\Exit_management;

use App\Http\Controllers\Controller;
use App\Models\InterviewQuestion;
use App\Models\InterviewQuestionResult;
use App\Models\Questions;
use App\Models\Question_Type;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class QuestionController extends Controller
{
    // save question type
    public function get_questions(Request $request)
    {
        $questions = DB::table('interview_question_type as qt') //question_type
            ->leftjoin('interview_question as iq', 'qt.id', '=', 'iq.type_id') // interview_question_type.id=interview_question.type_id
            ->where('qt.status', '=', '1') //interview_question_type
            ->where('iq.status', '=', '1') //interview_question_type
            ->where('qt.client_id', '=', $request->client_id) //approval_config.cid
            ->select(['qt.description as question_type_description', 'qt.title as question_type_title', 'qt.type as question_type', 'iq.statment as question', 'iq.id as question_id'])
            ->get();
        return response()->json(['success' => true, 'data' => $questions]);
    }
    public function save_question_types(Request $request)
    {
        $user_record = Auth::user();
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'description' => 'required',
            'question_type' => 'required',
            'question_type_id' => 'nullable|exists:interview_question_type,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        if (isset($request->question_type_id)) {
            $questions_type = Question_Type::find($request->question_type_id);
        } else {
            $questions_type = new Question_Type;
        }

        //save application
        // extract($request->all());
        $questions_type->client_id = $user_record->cid;
        $questions_type->created_by = $user_record->id;
        $questions_type->description = $request->description;
        $questions_type->title = $request->title;
        $questions_type->type = $request->question_type;

        if ($questions_type->save()) {

            return response()->json([
                'success' => true,
                'message' => 'Question type Add Successfully!!',
            ]);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'There is Some Issue!!',
            ]);
        }
    }
    
    //save question
    public function save_question(Request $request)
    {
        $user_record = Auth::user();
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'statement' => 'required',
            'question_type_id' => 'required|exists:interview_question_type,id',
            'question_id' => 'nullable|exists:interview_question_type,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        if (isset($request->question_id)) {
            $questions = Questions::find($request->question_id);
        } else {
            $questions = new Questions;
        }

        //save application
        // extract($request->all());
        $questions->client_id = $user_record->cid;
        $questions->created_by = $user_record->id;
        $questions->statment = $request->statement;
        $questions->type_id = $request->question_type_id;

        if ($questions->save()) {

            return response()->json([
                'success' => true,
                'message' => 'Question Add Successfully!!',
            ]);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'There is Some Issue!!',
            ]);
        }

    }
    public function save_interview_questions(Request $request)
    {
        //client_id emp_id resignation_id
        $exist = InterviewQuestionResult::where(['client_id' => $request->client_id, 'resignation_id' => $request->resignation_id, 'emp_id' => $request->emp_id])->count();
        if ($exist) {
            return response()->json([
                'success' => false,
                'message' => 'Your Interview is Already submitted',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'resignation_id' => 'required |exists:em_resignations,id',
            'emp_id' => 'required|exists:proll_employee,id',
            'client_id' => 'required|exists:proll_client,id',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        foreach ($request->questions as $req) {

            $validator = Validator::make($req, [
                'question_id' => 'required |exists:interview_question,id',
                'rating' => 'required',
                'type_id' => 'required|exists:interview_question_type,id',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
            $questions[] = array(
                'rating' => $req['rating'],
                'question_no' => $req['question_id'],
                'resignation_id' => $request->resignation_id,
                'client_id' => $request->client_id,
                'emp_id' => $request->emp_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            );
        }

        $result = InterviewQuestion::insert($questions);
        if ($result) {
            $interview_result = array(
                'client_id' => $request->client_id,
                'resignation_id' => $request->resignation_id,
                'emp_id' => $request->emp_id,
                'result' => $request->result,
                'rating_key' => $request->rating_key,
                'comment' => $request->comment,
            );
            $complete_result = InterviewQuestionResult::insert($interview_result);
            if ($complete_result) {
                return response()->json([
                    'success' => true,
                    'message' => 'successfully saved',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Error in save result',
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => 'error',
            ]);
        }
    }
    //Get Exit Interview
    public function get_exit_interview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resignation_id' => 'required |exists:em_resignations,id',
            'emp_id' => 'required|exists:proll_employee,id',
            'client_id' => 'required|exists:proll_client,id',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }

        $exit_interview=InterviewQuestion::where(['client_id' => $request->client_id, 'resignation_id' => $request->resignation_id, 'emp_id' => $request->emp_id])->get();
        $interview_result = InterviewQuestionResult::where(['client_id' => $request->client_id, 'resignation_id' => $request->resignation_id, 'emp_id' => $request->emp_id])->select('em_interview_result.*')->selectRaw("DATE_FORMAT(em_interview_result.created_at, '%d/%m/%Y') as formated_created_at")->get();
        $data['interview_question'] =  $exit_interview;
        $data['interview_result']=$interview_result;
        return response()->json(['success' => true, 'data' => $data]);
    }

}
