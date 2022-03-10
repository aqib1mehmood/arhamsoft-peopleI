<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\MultiApprovalHelpers;
use Illuminate\Support\Facades\Validator;

class MultiApprovalController extends Controller
{
    public function GetApplicationApprovelDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'client_id' => 'required',
            'application_id' => 'required',
            'module_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                    'code'=>'401'
                ],
                401);
        }
        $user_id = decrypt($request->user_id);
        $cid = decrypt($request->client_id);
        $application_id=$request->application_id;
        $module_id= $request->module_id;



        if (!empty($user_id)) {

            $data=MultiApprovalHelpers::get_application_approvel_detail($cid, $module_id, $application_id);
            $success = 'success';
            $code = '200';

        } else {
            $success = 'failure';
            $code = '201';
            $Expense = '';
        }
        return response()->json([
            'status' => $success,
            'code' => $code,
            'tile' => 'Approval Queue',
            'data' => (!empty($data)?$data:'This is old application will not be available in queue.')
        ]);
    }

}
