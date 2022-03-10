<?php

namespace App\Http\Controllers\api\Exit_management;

use App\Helpers\MultiApprovalHelpers;
use App\Http\Controllers\Controller;
use App\Models\EcfApprovelQueue;
use App\Models\FinanceClearance;
use App\Models\Resignation;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator;

class ExitManagementController extends Controller
{
    // protected $model;
    // function __construct(Resignation $model)
    // {
    //     $this->model = $model;
    // }
    public function index(Request $request)
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
        $resignations = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->where('em_resignations.client_id', $request->client_id)
            ->orderBy('created_at', 'desc')
            ->select('em_resignations.*', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as formated_created_at")
            ->selectRaw("DATE_FORMAT(em_resignations.last_working_day, '%d/%m/%Y') as last_working_day")
            ->get();
        foreach ($resignations as $resignation) {
            // dd($award);
            if ($resignation->app_status == 3) {
                $resignation->lm_status = $resignation->action_by_role == 2 ? 'Rejected' : 'Pending';
                $resignation->hr_status = $resignation->action_by_role == 3 ? 'Rejected' : 'Pending';
            } else {
                $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 2);
                $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 3);
                if ($lm_status == 0) {
                    $resignation->lm_status = 'Approved';
                } else {
                    $resignation->lm_status = 'Pending';
                }
                if ($hr_status == 0) {
                    $resignation->hr_status = 'Approved';
                } else {
                    $resignation->hr_status = 'Pending';
                }
                $resignation->From = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->created_at)->isoFormat('D-MMM-YYYY');
                $resignation->To = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->updated_at)->isoFormat('D-MMM-YYYY');
            }
        }
        $count = $resignations->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Resignation Application' : 'No Resignation Application Found!',
            'results' => $count > 0 ? $resignations : null,
        ]);
    }
    // Get applications which are ecf launched by hr @shahbaz
    public function get_ecf_approved_resignations(Request $request)
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
        $resignations = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->where('em_resignations.client_id', $request->client_id)
            ->where('em_resignations.ecf_launch_status',1)
            ->orderBy('created_at', 'desc')
            ->select('em_resignations.*', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as formated_created_at")
            ->get();
        foreach ($resignations as $resignation) {
            // dd($award);
            if ($resignation->app_status == 3) {
                $resignation->lm_status = $resignation->action_by_role == 2 ? 'Rejected' : 'Pending';
                $resignation->hr_status = $resignation->action_by_role == 3 ? 'Rejected' : 'Pending';
            } else {
                $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 2);
                $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 3);
                if ($lm_status == 0) {
                    $resignation->lm_status = 'Approved';
                } else {
                    $resignation->lm_status = 'Pending';
                }
                if ($hr_status == 0) {
                    $resignation->hr_status = 'Approved';
                } else {
                    $resignation->hr_status = 'Pending';
                }
                $resignation->From = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->created_at)->isoFormat('D-MMM-YYYY');
                $resignation->To = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->updated_at)->isoFormat('D-MMM-YYYY');
            }
        }
        $count = $resignations->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Resignation Application' : 'No Resignation Application Found!',
            'results' => $count > 0 ? $resignations : null,
        ]);
    }
    public function get_employee_resignation_application(Request $request)
    {
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
        //employee name
        //lm remarks
        //change create date formate
        //change comment to employee_remarks
        $app_url=env('APP_URL') . '/backend/storage/app/public/';
        $resignationApplication = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->leftjoin('approval_queue as q', 'q.application_id', 'em_resignations.id')
            ->leftjoin('proll_employee as e', 'e.id', 'q.approver_empid')
            ->where('q.role_id', 2)
            ->where('q.module_id', 5)
            ->where('em_resignations.client_id', $request->client_id)
            ->where('em_resignations.emp_id', $request->emp_id)
            ->orderBy('created_at', 'desc')
            ->select('em_resignations.*', 'em_resignations.comment as employee_remarks', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by', 'q.comments as lm_remarks', 'e.name as lm_name')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as notice_period_start_date")
            ->selectRaw("DATE_FORMAT(em_resignations.last_working_day, '%d/%m/%Y') as last_working_day")
            ->selectRaw("DATE_FORMAT(emp.contract_start_date, '%d/%m/%Y') as date_of_joining")
            ->selectRaw("CONCAT('$app_url',em_resignations.res_document) AS document_url")
            ->first();
        if (!empty($resignationApplication)) {
            return response()->json([
                'success' => true,
                'data' => $resignationApplication,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }
    public function get_resignation_application(Request $request)
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
        $app_url=env('APP_URL') . '/backend/storage/app/public/';
        $resignationApplication = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->leftjoin('approval_queue as q', 'q.application_id', 'em_resignations.id')
            ->leftjoin('proll_employee as e', 'e.id', 'q.approver_empid')
            ->where('q.role_id', 2)
            ->where('q.module_id', 5)
            ->where('em_resignations.client_id', $request->client_id)
            ->where('em_resignations.id', $request->resignation_id)
            ->orderBy('created_at', 'desc')
            ->select('em_resignations.*', 'em_resignations.comment as employee_remarks', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by', 'q.comments as lm_remarks', 'e.name as lm_name')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as notice_period_start_date")
            ->selectRaw("CONCAT('$app_url',em_resignations.res_document) AS document_url")
            ->first();
        // $resignationApplication = Resignation::where(['id' => $request->resignation_id, 'client_id' => $request->client_id])->get();
        if (!empty($resignationApplication)) {
            return response()->json([
                'success' => true,
                'data' => $resignationApplication,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    public function save_resignation_application(Request $request)
    {
        $exist = Resignation::where(['emp_id' => $request->emp_id, 'client_id' => $request->client_id, 'app_status' => 0])->count();

        if ($exist > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Your Resignation Application Already in process',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'seperation_reason' => 'required',
            'notice_period' => 'required',
            'last_working_date' => 'required|date_format:d/m/Y',
            'documents' => 'required',
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
        if (isset($request->resignation_id)) {
            $resignation = Resignation::find($request->resignation_id);
        } else {
            $resignation = new Resignation;
            $resignation->created_by = $request->emp_id;
        }
        if ($request->hasFile('documents')) {
            $path = Storage::disk('public')->put('resignation_documents', $request->documents);
            $resignation->res_document = $path;
        }
        //save application
        // extract($request->all());
        $resignation->client_id = $request->client_id;
        $resignation->emp_id = $request->emp_id;
        $resignation->seperation_reason = $request->seperation_reason;
        $resignation->notice_period = $request->notice_period;
        $resignation->last_working_day = Carbon::createFromFormat('d/m/Y', $request->last_working_date)->format('Y-m-d');
        $resignation->updated_by = $request->emp_id;
        if ($resignation->save()) {
            if (!isset($request->resignation_id)) {
                $data = MultiApprovalHelpers::enqueue_application_for_approval($request->client_id, 'exit', $resignation->id, $request->emp_id);
            }
            return response()->json([
                'success' => true,
                'message' => 'Resignation Add Successfully!!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'There is Some Issue!!',
            ]);
        }
    }
    //function change the Status of application
    public function change_resigntion_application_status(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'resignation_id' => 'required|exists:em_resignations,id',
            'client_id' => 'required|exists:proll_client,id',
            'role' => 'required|in:LM,HR',
            'status' => 'required|in:2,3,4',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $action_by_role = null;
        switch ($request->role) {
            case ('LM'):
                $action_by_role = 2;
                break;
            case ('HR'):
                $action_by_role = 3;
                break;
            case ('CEO'):
                $action_by_role = 5;
                break;
        }
        $is_action_taken = MultiApprovalHelpers::is_approver_take_action($request->client_id, 'exit', auth()->user()->id, $request->role, $request->resignation_id);
        if ($is_action_taken) {
            return response()->json([
                'success' => false,
                'message' => 'your already change the status',
            ]);
        }
        if ($request->status == 4) {
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue($request->client_id, 'exit', auth()->user()->id, $request->role, $request->resignation_id);
            if ($isInQueue) {
                $data = MultiApprovalHelpers::reset_approval_queue($request->client_id, 'exit', $request->resignation_id);
                $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . $request->status . "'," . "q.approver_view=" . "'" . "0" . "'," . "q.emp_view=" . "'" . "0" . "'";
                $status = MultiApprovalHelpers::update_approval_queue($request->client_id, 'exit', auth()->user()->id, $request->role, $request->resignation_id, $param);
                return response()->json([
                    'success' => true,
                    'message' => 'Status Change Sucessfully!!',
                ]);
            }
        }
        if ($request->status == 3) {
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue($request->client_id, 'exit', auth()->user()->id, $request->role, $request->resignation_id);
            if ($isInQueue) {
                $data = multiapprovalhelpers::delete_application_from_approval_queue($request->client_id, 'exit', $request->resignation_id);
                $resignation = Resignation::find($request->resignation_id);
                $resignation->app_status = $request->status;
                $resignation->updated_by = auth()->user()->id;
                $resignation->action_by_role = $action_by_role;
                if ($resignation->save()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Status Change Sucessfully!!',
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Somting wrong with rejection case!!',
                    ]);
                }
            }
        }
        $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "$request->status" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
        // $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->resignation_id, $param);
        $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'exit', auth()->user()->id, $request->role, $request->resignation_id, $param);
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'Status Change Sucessfully!!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you are not allowed to chnage status',
            ]);
        }
    }
    //function get dropdown values
    public function get_drop_down_value(Request $request)
    {
        $drop_down_name = $request->name;
        $data = DB::table('dynamic_dropdown_types')->where('display_text', $drop_down_name)->get()->first();
        $questions = DB::table('dynamic_dropdown_values')->where('type_id', $data->id)->get();
        return response()->json(['success' => true, 'data' => $questions]);
    }
    //function save ecf approvel ecf approvel queue
    public function ecf_approval_queue(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'resignation_id' => 'nullable|exists:em_resignations,id',
            'emp_id' => 'required|exists:proll_employee,id',
            'client_id' => 'required|exists:proll_client,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        foreach ($request->role as $role) {
            // ecf_exist
            $exist = MultiApprovalHelpers::ecf_exist($request->client_id, $request->resignation_id, $role);
            if ($exist) {
                continue;
            }
            $list[] = array(
                'client_id' => $request->client_id,
                'resignation_id' => $request->resignation_id,
                'emp_id' => $request->emp_id,
                'role' => strtolower($role),
            );
        }
        if (!empty($list)) {
            $result = EcfApprovelQueue::insert($list);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'ECF successfully Approved',
            ]);
        }
        if ($result) {
            $flag=false;
            $approval_config = multiapprovalhelpers::get_approval_config('exit', $request->client_id);
            foreach ($approval_config as $config) {
                if ($config->role == "HR") {
                    $approvers_ids = multiapprovalhelpers::get_approvers_ids($request->emp_id, $config->role, $config->level_count, $config->module_id, $request->client_id, $config->role_id);
                    foreach ($approvers_ids as $approver) {
                        $param = "q.comments=" . "'" . "approved" . "'," . "q.status=" . "'" . "2" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
                        $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'exit', $approver->empid, $config->role, $request->resignation_id, $param);
                        if(!$data)
                        $flag=true;
                    }
                }
            }
            if($flag){
                return response()->json([
                    'success' => false,
                    'message' => 'Some thing is wrong during change hr status',
                ]);
            }
            $resignation = Resignation::find($request->resignation_id);
            if ($resignation) {
                $resignation->ecf_launch_status = 1;

                if ($resignation->save()) {

                    return response()->json([
                        'success' => true,
                        'message' => 'ECF successfully Approved',
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Error to publisehd ECF',
                    ]);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error to publisehd ECF',
            ]);
        }
    }
    //get ecf approvel queue
    //Get ECF launch resignation application according to role @shahbaz

    public function get_ecf_launch_resignation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:proll_client,id',
            'role' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $resignations = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->leftjoin('ecf_approvel_queue as ecf', 'ecf.resignation_id', 'em_resignations.id')
            ->where('ecf.client_id', $request->client_id)
            ->where('ecf.role', strtolower($request->role))
            ->orderBy('id', 'desc')
            ->select('em_resignations.*', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by', 'ecf.role as approver_role', 'ecf.remarks as approver_remarks', 'ecf.status as ecf_status')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as formated_created_at")
            ->get();
        foreach ($resignations as $resignation) {
            $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 2);
            $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'exit', $resignation->id, 3);
            if ($lm_status == 0) {
                $resignation->lm_status = 'Approved';
            } else {
                $resignation->lm_status = 'Pending';
            }
            if ($hr_status == 0) {
                $resignation->hr_status = 'Approved';
            } else {
                $resignation->hr_status = 'Pending';
            }
            $resignation->From = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->created_at)->isoFormat('D-MMM-YYYY');
            $resignation->To = Carbon::createFromFormat('Y-m-d H:i:s', $resignation->updated_at)->isoFormat('D-MMM-YYYY');
        }
        $count = $resignations->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Resignation Application' : 'No Resigntion Application Found!',
            'results' => $count > 0 ? $resignations : null,
        ]);
    }

    // Get ecf resignation application @shahbaz
    public function get_ecf_resignation_application(Request $request)
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
        $app_url=env('APP_URL') . '/backend/storage/app/public/';
        $resignationApplication = Resignation::leftjoin('proll_employee as emp', 'emp.id', 'em_resignations.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'em_resignations.updated_by')
            ->leftjoin('approval_queue as q', 'q.application_id', 'em_resignations.id')
            ->leftjoin('proll_employee as e', 'e.id', 'q.approver_empid')
            ->where('q.role_id', 2)
            ->where('q.module_id', 5)
            ->where('em_resignations.client_id', $request->client_id)
            ->where('em_resignations.id', $request->resignation_id)
            ->orderBy('created_at', 'desc')
            ->select('em_resignations.*', 'em_resignations.comment as employee_remarks', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'update_by.name as update_by', 'q.comments as lm_remarks', 'e.name as lm_name')
            ->selectRaw("DATE_FORMAT(em_resignations.created_at, '%d/%m/%Y') as notice_period_start_date")
            ->selectRaw("CONCAT('$app_url',em_resignations.res_document) AS document_url")
            ->first();
        $ecf_approval = EcfApprovelQueue::where(['client_id' => $request->client_id, 'resignation_id' => $request->resignation_id])->get();
        $data['resignationApplication'] = $resignationApplication;
        $data['ecf_approval_queue'] = $ecf_approval;
        if (!empty($data)) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }
    // save finance clearance
    public function save_finance_clearance(Request $request)
    {
        foreach ($request->all() as $req) {

            $validator = Validator::make($req, [
                'resignation_id' => 'required|exists:em_resignations,id',
                'emp_id' => 'required|exists:proll_employee,id',
                'client_id' => 'required|exists:proll_client,id',
                'type' => 'required|in:1,2,3',
                'status' => 'required|in:0,1,2',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->messages(),
                ]);
            }
            $data[] = array(
                'type' => $req['type'],
                'description' => $req['description'],
                'amount' => $req['amount'],
                'status' => $req['status'],
                'resignation_id' => $req['resignation_id'],
                'client_id' => $req['client_id'],
                'emp_id' => $req['emp_id'],
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            );
        }
        $result = FinanceClearance::insert($data);
        if ($result) {
            if ($request) {
                $resignation_id = $request[0]['resignation_id'];
                $client_id = $request[0]['client_id'];
            }
            $res = MultiApprovalHelpers::change_ecf_status($client_id, $resignation_id, 'finance', 2);
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
    function final_settlement(Request $request){
        dd('working');
    }
}
