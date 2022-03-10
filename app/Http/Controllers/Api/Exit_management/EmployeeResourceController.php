<?php

namespace App\Http\Controllers\api\Exit_management;
use App\Helpers\MultiApprovalHelpers;
use App\Http\Controllers\Controller;
use App\Models\AssetClearance;
use App\Models\EmployeeResource;
use Auth;
use Illuminate\Http\Request;
use Validator;

class EmployeeResourceController extends Controller
{
    //get assets
    public function get_assets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $assets = EmployeeResource::leftjoin('proll_employee as emp','emp.id','employee_resource.taking_over_id')
        ->where(['employee_resource.type' => 2, 'employee_resource.client_id' => $request->client_id])
        ->select('employee_resource.*','emp.name as taking_over_employee')
        ->get();
        return response()->json(['success' => true, 'data' => $assets]);
    }
    //get responsibility
    public function get_responsibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $responsibility = EmployeeResource::leftjoin('proll_employee as emp','emp.id','employee_resource.taking_over_id')
        ->where(['employee_resource.type' => 1, 'employee_resource.client_id' => $request->client_id])
        ->select('employee_resource.*','emp.name as taking_over_employee')
        ->get();
        return response()->json(['success' => true, 'data' => $responsibility]);
    }
    //get employee assets
    public function get_employee_assets(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
            'resignation_id' => 'required|exists:em_resignations,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $assets = EmployeeResource::leftjoin('proll_employee as emp','emp.id','employee_resource.taking_over_id')
        ->where(['employee_resource.type' => 2, 'employee_resource.client_id' => $request->client_id,'employee_resource.resignation_id' => $request->resignation_id])
        ->select('employee_resource.*','emp.name as taking_over_employee')
        ->get();
        return response()->json(['success' => true, 'data' => $assets]);
    }
    //get employee responsibility
    public function get_employee_responsibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
            'resignation_id' => 'required|exists:em_resignations,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $responsibility = EmployeeResource::leftjoin('proll_employee as emp','emp.id','employee_resource.taking_over_id')
        ->where(['employee_resource.type' => 1, 'employee_resource.client_id' => $request->client_id,'employee_resource.resignation_id' => $request->resignation_id])
        ->select('employee_resource.*','emp.name as taking_over_employee')
        ->get();
        return response()->json(['success' => true, 'data' => $responsibility]);
    }
    // save employee assets
    public function save_employee_assets(Request $request)
    {
        $flag = false;
        $newassests = [];
        $oldassest = [];
        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'title' => 'required',
                'description' => 'required',
                'hand_over_id' => 'required|exists:proll_employee,id',
                'hand_over_date' => 'required',

                // 'role' => 'required|in:LM,HR',
                'resignation_id' => 'nullable|exists:em_resignations,id',
                'emp_id' => 'required|exists:proll_employee,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
            if(empty($req['asset_id'])){
                
                $newassests[] = array(
                    'title' => $req['title'],
                    'description' => $req['description'],
                    'resignation_id' => $req['resignation_id'],
                    'handing_over_status' => $req['handing_over_status'],
                    'client_id' => $req['client_id'],
                    'taking_over_id' => $req['hand_over_id'],
                    'emp_id' => $req['emp_id'],
                    'taking_over_date' => $req['hand_over_date'],
                    'type' => 2,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                );
              
            }else{
                $oldassests[] = array(
                    'title' => $req['title'],
                    'description' => $req['description'],
            
                    'handing_over_status' => $req['handing_over_status'],
                
                    'taking_over_id' => $req['hand_over_id'],
            
                    'taking_over_date' => $req['hand_over_date'],
                    'asset_id'=>$req['asset_id'],
                    'updated_by' => auth()->user()->id,
                );
            }
            
        }
        
        if(!empty($newassests)){
            $result = EmployeeResource::insert($newassests);
        }else{
            $result=1;
        }
       
        if(!empty($oldassests)){
            
            foreach ($oldassests as $req) {
               
                $assets = array(
                    'title' => $req['title'],
                    'description' => $req['description'],
                    'taking_over_id' => $req['taking_over_id'],
                    'taking_over_date' => $req['taking_over_date'],
                    'updated_by' => auth()->user()->id,
                );
            
                $updateresult = EmployeeResource::where('id', $req['asset_id'])->update($assets);
                if ($updateresult) {
    
                } else {
                    $flag = true;
                }
        }
    }

    
        if ($result) {
            if (!$flag) {
                return response()->json([
                    'success' => true,
                    'message' => 'successfully saved your Assets',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error in update Assets',
                ]);
            }
                
        } else {
            return response()->json([
                'success' => false,
                'message' => 'error',
            ]);
        }
    }
    // save employee responsibility
    public function save_employee_responsibility(Request $request)
    {
        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'title' => 'required',
                'description' => 'required',
                'hand_over_id' => 'required|exists:proll_employee,id',
                'hand_over_date' => 'required',
                // 'role' => 'required|in:LM,HR',
                'resignation_id' => 'nullable|exists:em_resignations,id',
                'emp_id' => 'required|exists:proll_employee,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
            $assests[] = array(
                'title' => $req['title'],
                'description' => $req['description'],
                'resignation_id' => $req['resignation_id'],
                'handing_over_status' => $req['handing_over_status'],
                'client_id' => $req['client_id'],
                'taking_over_id' => $req['hand_over_id'],
                'emp_id' => $req['emp_id'],
                'taking_over_date' => $req['hand_over_date'],
                'type' => 1,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            );
        }
        $result = EmployeeResource::insert($assests);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'successfully saved',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'error',
            ]);
        }
    }
    //update employee assets
    public function update_employee_assets(Request $request)
    {
        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'title' => 'required',
                'description' => 'required',
                'hand_over_id' => 'required|exists:proll_employee,id',
                'hand_over_date' => 'required',
                'assets_id' => 'required|exists:employee_resource,id',
                // 'role' => 'required|in:LM,HR',
                'resignation_id' => 'nullable|exists:em_resignations,id',
                // 'emp_id' => 'required|exists:proll_employee,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
        }
        $flag = false;
        foreach ($request->all() as $req) {
            $assest = array(
                'title' => $req['title'],
                'description' => $req['description'],
                'taking_over_id' => $req['hand_over_id'],
                'taking_over_date' => $req['hand_over_date'],
                'updated_by' => auth()->user()->id,

            );
            $result = EmployeeResource::where('id', $req['assets_id'])->update($assest);
            if ($result) {

            } else {
                $flag = true;
            }
        }
        //response after update data
        if (!$flag) {
            return response()->json([
                'success' => true,
                'message' => 'successfully saved your Assets',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error in update Assets',
            ]);
        }

    }
    //update employee responsibility
    public function update_employee_responsibility(Request $request)
    {
        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'title' => 'required',
                'description' => 'required',
                'hand_over_id' => 'required|exists:proll_employee,id',
                'hand_over_date' => 'required',
                'responsibility_id' => 'required|exists:employee_resource,id',
                // 'role' => 'required|in:LM,HR',
                'resignation_id' => 'nullable|exists:em_resignations,id',
                // 'emp_id' => 'required|exists:proll_employee,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
        }
        $flag = false;
        foreach ($request->all() as $req) {
            $assest = array(
                'title' => $req['title'],
                'description' => $req['description'],
                'taking_over_id' => $req['hand_over_id'],
                'taking_over_date' => $req['hand_over_date'],
                'updated_by' => auth()->user()->id,
            );
            $result = EmployeeResource::where('id', $req['responsibility_id'])->update($assest);
            if ($result) {

            } else {
                $flag = true;
            }
        }
        //response after update data
        if (!$flag) {
            return response()->json([
                'success' => true,
                'message' => 'successfully update you responsibility',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error in update responsibility',
            ]);
        }
    }
    //update assets status from Admin
    public function update_assets_status_by_admin(Request $request)
    {

        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'asset_condition_status' => 'required',
                'asset_id' => 'required|exists:employee_resource,id',
                'emp_id' => 'required|exists:proll_employee,id',
                'resignation_id' => 'nullable|exists:em_resignations,id',
                'client_id' => 'required|exists:proll_client,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
            $assests[] = array(
                'client_id' => $req['client_id'],
                'emp_id' => $req['emp_id'],
                'resignation_id' => $req['resignation_id'],
                'acceptance_status' => $req['asset_condition_status'],
                'client_id' => $req['client_id'],
                'employee_resource_id' => $req['asset_id'],
                'over_used_cost' => isset($req['extra_cost']) ? $req['extra_cost'] : '',
            );
        }

        if (!empty($assests)) {
            $result = AssetClearance::insert($assests);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'successfully saved',
            ]);
        }

        if ($result) {
            if ($request) {
                $resignation_id = $request[0]['resignation_id'];
                $client_id = $request[0]['client_id'];
            }
            $res = MultiApprovalHelpers::change_ecf_status($client_id, $resignation_id, 'admin', 2);

            if ($res) {
                return response()->json([
                    'success' => true,
                    'message' => 'successfully saved',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'error in change status',
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => 'error',
            ]);
        }
    }
    //delete Asset
    public function delete_asset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:employee_resource,id',
            'client_id' => 'required|exists:proll_client,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $asset = EmployeeResource::find($request->asset_id)->forceDelete();
        if ($asset) {
            return response()->json([
                'success' => true,
                'message' => 'Asset deleted Successfully!!',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Access denied',
        ]);
    }
    //delete responsibility
    public function delete_responsibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'responsibility_id' => 'required|exists:employee_resource,id',
            'client_id' => 'required|exists:proll_client,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $responsibility = EmployeeResource::find($request->responsibility_id)->forceDelete();
        if ($responsibility) {
            return response()->json([
                'success' => true,
                'message' => 'Asset deleted Successfully!!',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Access denied',
        ]);
    }
}
