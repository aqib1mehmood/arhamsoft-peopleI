<?php

namespace App\Http\Controllers\api\Exit_management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use Validator;
use App\Models\FinanceClearance;
use App\Models\FinalSettlement;
use App\Models\Resignation;
class FinalSettlementController extends Controller
{
    //get employee salary
    function get_employee_salary(Request $request){
        
        $data = DB::table('cb_salary_details')->where('people_code',auth()->user()->empcode)->get();
        if(!empty($data)){
            return response()->json([
                'success' => false,
                'results' => $data,
            ]);
        }else{
            return response()->json([
                'success' => false,
                'results' => [],
            ]);
        }
    }
    //save final settlement
    function final_settlement(Request $request){
        $validator = Validator::make($request->all(), [
            'payable_amount' => 'required',
            'resignation_id' => 'nullable|exists:em_resignations,id',
            'client_id' => 'required|exists:proll_client,id',
            'emp_id' => 'required|exists:proll_employee,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $FinalSettlement = new FinalSettlement();
        $FinalSettlement->client_id = $request->client_id;
        $FinalSettlement->resignation_id = $request->resignation_id;
        $FinalSettlement->comment = $request->comment ? $request->comment : '';
        $FinalSettlement->payable_amount = $request->payable_amount;
        $FinalSettlement->emp_id  = $request->emp_id;
        $FinalSettlement->created_by = auth()->user()->id;
        $FinalSettlement->updated_by = auth()->user()->id;
        if($FinalSettlement->save()){
            $application = Resignation::find($request->resignation_id);
            $application->app_status = 2;
            if($application->save()){
                return response()->json([
                    'success' => false,
                    'message' => 'Successfully saved',
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Error in changed Status',
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Error in saved final settlement',
            ]);
        }
    }
    //employee dues
    function get_employee_dues(Request $request){
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
            'emp_id' => 'required|exists:proll_employee,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        //employee loan
        $loandata = DB::table('la_requests as lr')->leftjoin('la_installments as li','li.la_req_id','=','lr.loan_req_id')->where(['lr.empid'=>$request->emp_id,'lr.application_status'=>2,'li.status'=>1])->select('lr.loan_amount')->selectRaw('(lr.loan_amount-SUM(li.monthly_installment)) as remain_loan_amount')->get();
        //advence salary
    }
}
