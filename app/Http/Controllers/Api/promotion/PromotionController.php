<?php

namespace App\Http\Controllers\Api\promotion;

use App\Helpers\multiapprovalhelpers;
use App\Http\Controllers\Controller;
use App\Models\Documents;
use App\Models\Employee;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

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
        $created_by = auth()->user()->id;
        $promotions = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
            ->leftjoin('proll_employee as create_by', 'create_by.id', 'pr_promotions.created_by')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'pr_promotions.updated_by')
            ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
            ->leftjoin('pr_appraisal_types as types', 'types.id', 'pr_promotions.appraisal_type')
            ->orderBy('created_at', 'desc')
            ->where('pr_promotions.client_id', $request->client_id)
            ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as department_name', 'design.designation_name', 'due_to.name as Promotion_due_to', 'create_by.name as create_by', 'update_by.name as update_by', 'types.name as appraisal_type')
            ->selectRaw("CASE WHEN pr_promotions.created_by  = $created_by THEN 'true' ELSE 'false' END AS award_owner_status")
            ->get();
        foreach ($promotions as $promotion) {
            if ($promotion->promotion_status == 3) {
                $promotion->lm_status = $promotion->action_by_role == 2 ? 'Rejected' : 'Approved';
                $promotion->hr_status = $promotion->action_by_role == 3 ? 'Rejected' : 'Pending';
                $promotion->ceo_status = $promotion->action_by_role == 5 ? 'Rejected' : 'Pending';
            } else {
                $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 2);
                $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 3);
                $ceo_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 5);
                if ($lm_status == 0) {
                    $promotion->lm_status = 'Approved';
                } else {
                    $promotion->lm_status = 'Pending';
                }
                if ($hr_status == 0) {
                    $promotion->hr_status = 'Approved';
                } else {
                    $promotion->hr_status = 'Pending';
                }
                if ($ceo_status == 0) {
                    $promotion->ceo_status = 'Approved';
                } else {
                    $promotion->ceo_status = 'Pending';
                }
            }
            $Roles = Employee::User_Roles_v2_3($promotion->created_by, $promotion->client_id);

            // $Roless = [];
            // if($Roles){
            //     foreach($Roles as $index => $role){
            //         if($role->name == 'Employee'){
            //             unset($Roles[$index]);
            //             continue;
            //         }
            //         array_push($Roless,$role);
            //     }
            // }
            $promotion->initiated_by = $promotion->created_by_role;
            $promotion->From = Carbon::createFromFormat('Y-m-d H:i:s', $promotion->created_at)->isoFormat('D-MMM-YYYY');
            $promotion->To = Carbon::createFromFormat('Y-m-d H:i:s', $promotion->updated_at)->isoFormat('D-MMM-YYYY');
        }
        $count = $promotions->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotions Found' : 'No Promotion Found!',
            'results' => $count > 0 ? $promotions : null,
        ]);
    }
    public function ceo_promotion_list(Request $request)
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
        $promotions[] = '';
        $applications = DB::select(DB::raw("SELECT aq1.application_id,aq1.comments,aq1.role_id,p.name FROM `approval_queue` aq1 left join proll_employee p ON aq1.approver_empid=p.id WHERE (select count(*) from approval_queue aq2 where  module_id = 10 and  aq1.application_id=aq2.application_id and aq2.status != 2 ) = 0 group by aq1.application_id;"));
        // $applications = DB::select(DB::raw("SELECT application_id FROM `approval_queue` aq1 WHERE (select count(*) from approval_queue aq2 where  module_id = 10 and  aq1.application_id=aq2.application_id and aq2.status != 2 ) = 0 group by aq1.application_id;"));
        foreach ($applications as $index => $application) {
            $promotions[$index] = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
                ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
                ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
                ->leftjoin('proll_employee as create_by', 'create_by.id', 'pr_promotions.created_by')
                ->leftjoin('proll_employee as update_by', 'update_by.id', 'pr_promotions.updated_by')
                ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
                ->leftjoin('pr_appraisal_types as types', 'types.id', 'pr_promotions.appraisal_type')
                ->where('pr_promotions.promotion_status', '!=', 3)
                ->where('pr_promotions.client_id', $request->client_id)
                ->where('pr_promotions.id', $application->application_id)
                ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as department_name', 'design.designation_name', 'due_to.name as Promotion_due_to', 'create_by.name as create_by', 'update_by.name as update_by', 'types.name as appraisal_type')
                ->first();
            if (!empty($promotions[$index])) {
                $promotions[$index]->hr_status = 'Approved';
                $promotions[$index]->approve_status = false;
                $Roles = Employee::User_Roles_v2_3($promotions[$index]->created_by, $request->client_id);
                $promotions[$index]->initiated_by = $Roles;
                $promotions[$index]->From = Carbon::createFromFormat('Y-m-d H:i:s', $promotions[$index]->created_at)->isoFormat('D-MMM-YYYY');
                $promotions[$index]->To = Carbon::createFromFormat('Y-m-d H:i:s', $promotions[$index]->updated_at)->isoFormat('D-MMM-YYYY');
                // $awards[$index]->toArray();
                // dump($promotions);
            } else {
                // unset($awards[$index]);
            }

        }
        // dd($promotions);

        $promotion = [];
        foreach ($promotions as $key => $value) {
            if ($value) {
                array_push($promotion, $value);

            }
        }

        // $award = array_filter($awards, function ($value) {return !is_null($value) && $value !== '';});
        //  dd((array)$awards);
        $count = count($promotion);
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotion Found' : 'No Promotion Found!',
            'results' => $count > 0 ? $promotion : null,
        ]);
    }

    public function get_recent_promotion(Request $request)
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
        $promotions = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
            ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
            ->leftjoin('proll_client_location as location', 'location.loc_id', 'pr_promotions.location_id')
            ->orderBy('created_at', 'desc')
            ->where('pr_promotions.client_id', $request->client_id)
            ->whereNull('pr_promotions.deleted_at')
            ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as department_name', 'design.designation_name', 'due_to.name as Promotion_due_to', 'location.loc_desc as location_description', 'location.address as location_address')
            ->latest()
            ->take(5)
            ->get();
        foreach ($promotions as $promotion) {
            if ($promotion->promotion_status == 3) {
                $promotion->lm_status = $promotion->action_by_role == 2 ? 'Rejected' : 'Approved';
                $promotion->hr_status = $promotion->action_by_role == 3 ? 'Rejected' : 'Approved';
                $promotion->ceo_status = $promotion->action_by_role == 5 ? 'Rejected' : 'Pending';
            } else {
                $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 2);
                $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 3);
                $ceo_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'promotion', $promotion->id, 5);
                if ($lm_status == 0) {
                    $promotion->lm_status = 'Approved';
                } else {
                    $promotion->lm_status = 'Pending';
                }
                if ($hr_status == 0) {
                    $promotion->hr_status = 'Approved';
                } else {
                    $promotion->hr_status = 'Pending';
                }
                if ($ceo_status == 0) {
                    $promotion->ceo_status = 'Approved';
                } else {
                    $promotion->ceo_status = 'Pending';
                }
            }
            $Roles = Employee::User_Roles_v2_3($promotion->created_by, $promotion->client_id);
            $promotion->intiated_by = $Roles;
            $promotion->From = Carbon::createFromFormat('Y-m-d H:i:s', $promotion->created_at)->isoFormat('D-MMM-YYYY');
            $promotion->To = Carbon::createFromFormat('Y-m-d H:i:s', $promotion->updated_at)->isoFormat('D-MMM-YYYY');
        }
        $count = $promotions->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotions Found' : 'No Promotion Found!',
            'results' => $count > 0 ? $promotions : null,
        ]);
    }

    public function get_promotion_via_employee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required|exists:proll_employee,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $promotion = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
            ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
            ->leftjoin('pr_appraisal_types as appraisal', 'appraisal.id', 'pr_promotions.appraisal_type')
            ->leftjoin('pr_promotion_types as types', 'types.id', 'pr_promotions.promotion_type')
            ->leftjoin('employee_bands as band', 'band.id', 'pr_promotions.band_id')
            ->leftjoin('proll_client_location as location', 'location.loc_id', 'pr_promotions.location_id')
            ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as proposed_department', 'due_to.name as Promotion_due_to', 'appraisal.name as Appraisal_name', 'design.designation_name as proposed_designation', 'types.name as promotion_types', 'band.band_desc as proposed_band', 'location.loc_desc as location_description', 'location.address as location_address')
            ->where('emp_id', $request->emp_id)
            ->get();
        $count = $promotion->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotion Found' : 'No Promotion Found!',
            'results' => $count > 0 ? $promotion : null,
        ]);
    }

    public function promotion_filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required|exists:proll_employee,id',
            'appraisal_type' => 'required|exists:pr_appraisal_types,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $promotion = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
            ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
            ->leftjoin('pr_appraisal_types as appraisal', 'appraisal.id', 'pr_promotions.appraisal_type')
            ->leftjoin('pr_promotion_types as types', 'types.id', 'pr_promotions.promotion_type')
            ->leftjoin('employee_bands as band', 'band.id', 'pr_promotions.band_id')
            ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as proposed_department', 'due_to.name as Promotion_due_to', 'appraisal.name as Appraisal_name', 'design.designation_name as proposed_designation', 'types.name as promotion_types', 'band.band_desc as proposed_band')
            ->where('emp_id', $request->emp_id)
            ->where('appraisal_type', $request->appraisal_type)
            ->get();
        $count = $promotion->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotion Found' : 'No Promotion Found!',
            'results' => $count > 0 ? $promotion : null,
        ]);
    }

    public function show_promotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pr_promotions,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $created_by=auth()->user()->id;
        $promotion = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'pr_promotions.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'pr_promotions.designation_id')
            ->leftjoin('pr_promotion_reasons as due_to', 'due_to.id', 'pr_promotions.promotion_reason')
            ->leftjoin('pr_appraisal_types as appraisal', 'appraisal.id', 'pr_promotions.appraisal_type')
            ->leftjoin('pr_promotion_types as types', 'types.id', 'pr_promotions.promotion_type')
            ->leftjoin('employee_bands as band', 'band.id', 'pr_promotions.band_id')
            ->leftjoin('proll_client_location as location', 'location.loc_id', 'pr_promotions.location_id')
            ->leftjoin('approval_queue as q', 'q.application_id', 'pr_promotions.id')
            ->leftjoin('approval_queue as q2', 'q2.application_id', 'pr_promotions.id')
            ->leftjoin('proll_employee as e', 'e.id', 'q.approver_empid')
            ->leftjoin('proll_employee as e2', 'e2.id', 'q2.approver_empid')
            ->where('q.role_id', 3)
            ->where('q2.role_id', 2)
            ->where('q.module_id', 10)
            ->where('pr_promotions.id', $request->id)
            ->select('pr_promotions.*', 'emp.id as employee_id', 'emp.name as employee_name', 'depart.department_name as proposed_department', 'due_to.name as Promotion_due_to', 'appraisal.name as Appraisal_name', 'design.designation_name as proposed_designation', 'types.name as promotion_types', 'band.band_desc as proposed_band', 'location.loc_desc as location_description', 'location.address as location_address', 'q.comments as hr_comment', 'e.name as hr_name', 'e2.name as lm_name', 'q2.comments as lm_comment', 'e2.name as lm_name')
            ->selectRaw("CASE WHEN pr_promotions.created_by  = $created_by THEN 'true' ELSE 'false' END AS withdraw_status")
            ->orderBy('q2.id','DESC')
            ->first();
            $count = 0;
            if($promotion){
                $count = $promotion->count();
            }
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Promotion Found' : 'This Application is Rejected!',
            'results' => $count > 0 ? [$promotion] : null,
        ]);
    }

    public function show_promotion_attachments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pr_promotions,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $apromotion_attachment = Promotion::find($request->id);
        if (!empty($apromotion_attachment)) {
            $apromotion_attachment = $apromotion_attachment->documents;
            foreach ($apromotion_attachment as $data) {
                $data->url = env('APP_URL') . '/backend/storage/app/public/' . $data->url;
            }
            $count = $apromotion_attachment->count();
            return response()->json([
                'success' => $count > 0 ? true : false,
                'message' => $count > 0 ? 'Attachment Found' : 'No Attachment Found!',
                'results' => $count > 0 ? $apromotion_attachment : null,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No Attachment Found!',
                'results' => null,
            ]);
        }

    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function change_promotion_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:pr_promotions,id',
            'client_id' => 'required|exists:proll_client,id',
            'role' => 'required|in:LM,HR,CEO',
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
        if ($request->status == 4) {
            if ($request->role == 'CEO') {
                $approval_config = multiapprovalhelpers::get_approval_config('award', $request->client_id);

                foreach ($approval_config as $config) {
                    // dump($config);
                    $priority = 1;

                    if ($config->role == 'CEO') {
                        $approvers = array(
                            'comments' => $request->comment,
                        );
                        $module_id = $config->module_id;
                        $approvalQueue = DB::table('approval_queue')->where(['cid' => $request->client_id, 'approval_type' => $config->approval_type, 'role_id' => $config->role_id, 'approver_empid' => auth()->user()->id, 'application_id' => $request->application_id, 'module_id' => $config->module_id])->first();
                        if ($approvalQueue) {
                            DB::table('approval_queue')->where('id', $approvalQueue->id)
                                ->update($approvers);

                        } else {
                            $approvers['cid'] = $request->client_id;
                            $approvers['approval_type'] = $config->approval_type;
                            $approvers['role_id'] = $config->role_id;
                            $approvers['approver_empid'] = auth()->user()->id;
                            $approvers['application_id'] = $request->application_id;
                            $approvers['module_id'] = $module_id;
                            $approvers['priority'] = $priority;
                            $approvers['max_count'] = 2;
                            $approvers['created_at'] = date('y-m-d h:i:s');
                            $approvers['status'] = $request->status;
                            DB::table('approval_queue')
                                ->insert($approvers);
                        }
                    }

                }

            }
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id);

            if ($isInQueue) {
            $data = MultiApprovalHelpers::reset_approval_queue($request->client_id, 'promotion', $request->application_id);
            $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . $request->status . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
            $status = MultiApprovalHelpers::update_approval_queue($request->client_id, 'promotion', auth()->user()->id, $request->role, $request->application_id, $param);
            return response()->json([
                'success' => true,
                'message' => 'Status Change Sucessfully!!',
            ]);
        }
        }

        if ($request->status == 3) {
            if ($request->role == 'CEO') {
                $approval_config = multiapprovalhelpers::get_approval_config('award', $request->client_id);

                foreach ($approval_config as $config) {
                    // dump($config);
                    $priority = 1;

                    if ($config->role == 'CEO') {
                        $approvers = array(
                            'comments' => $request->comment,
                        );
                        $module_id = $config->module_id;
                        $approvalQueue = DB::table('approval_queue')->where(['cid' => $request->client_id, 'approval_type' => $config->approval_type, 'role_id' => $config->role_id, 'approver_empid' => auth()->user()->id, 'application_id' => $request->application_id, 'module_id' => $config->module_id])->first();
                        if ($approvalQueue) {
                            DB::table('approval_queue')->where('id', $approvalQueue->id)
                                ->update($approvers);

                        } else {
                            $approvers['cid'] = $request->client_id;
                            $approvers['approval_type'] = $config->approval_type;
                            $approvers['role_id'] = $config->role_id;
                            $approvers['approver_empid'] = auth()->user()->id;
                            $approvers['application_id'] = $request->application_id;
                            $approvers['module_id'] = $module_id;
                            $approvers['priority'] = $priority;
                            $approvers['max_count'] = 2;
                            $approvers['created_at'] = date('y-m-d h:i:s');
                            $approvers['status'] = 4;
                            DB::table('approval_queue')
                                ->insert($approvers);
                        }
                    }

                }

            }
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id);

            if ($isInQueue) {
            $data = multiapprovalhelpers::delete_application_from_approval_queue($request->client_id, 'promotion', $request->application_id);
            $promotion = Promotion::find($request->application_id);
            $promotion->promotion_status = $request->status;
            $promotion->updated_by = auth()->user()->id;
            $promotion->action_by_role=$action_by_role;
            $promotion->save();
            return response()->json([
                'success' => true,
                'message' => 'Status Change Sucessfully!!',
            ]);
        }
        }
        if ($request->role == 'CEO') {
            $approval_config = multiapprovalhelpers::get_approval_config('promotion', $request->client_id);

            foreach ($approval_config as $config) {
                // dump($config);
                $priority = 1;

                if ($config->role == 'CEO') {
                    $approvers = array(
                        'comments' => $request->comment,
                    );
                    $module_id = $config->module_id;
                    $approvalQueue = DB::table('approval_queue')->where(['cid' => $request->client_id, 'approval_type' => $config->approval_type, 'role_id' => $config->role_id, 'approver_empid' => auth()->user()->id, 'application_id' => $request->application_id, 'module_id' => $config->module_id])->first();
                    if ($approvalQueue) {
                        DB::table('approval_queue')->where('id', $approvalQueue->id)
                            ->update($approvers);

                    } else {
                        $approvers['cid'] = $request->client_id;
                        $approvers['approval_type'] = $config->approval_type;
                        $approvers['role_id'] = $config->role_id;
                        $approvers['approver_empid'] = auth()->user()->id;
                        $approvers['application_id'] = $request->application_id;
                        $approvers['module_id'] = $module_id;
                        $approvers['priority'] = $priority;
                        $approvers['max_count'] = 2;
                        $approvers['created_at'] = date('y-m-d h:i:s');
                        $approvers['status'] = 2;
                        DB::table('approval_queue')
                            ->insert($approvers);
                    }
                }

            }
        }

        $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "$request->status" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
        $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'promotion', auth()->user()->id, $request->role, $request->application_id, $param);
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'Status Change Sucessfully!!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'required|exists:proll_employee,id',
            'promotion_type' => 'required|exists:pr_promotion_types,id',
            'appraisal_type' => 'required|exists:pr_appraisal_types,id',
            'promotion_reason' => 'required|exists:pr_promotion_reasons,id',
            'department_id' => 'required|exists:department_hierarchy,id',
            'designation_id' => 'required|exists:proll_client_designation,designation_id',
            'band_id' => 'required|exists:employee_bands,id',
            'location_id' => 'required|exists:proll_client_location,loc_id',
            'amount' => 'required|integer',
            'brief_reason' => 'required',
            'documents' => 'required',
            'role' => 'required|in:LM,HR',
            'promotion_id' => 'nullable|exists:pr_promotions,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        if (isset($request->promotion_id)) {
            $promotion = Promotion::find($request->promotion_id);
        } else {
            $promotion = new Promotion;
        }
        $promotion->emp_id = $request->emp_id;
        $promotion->promotion_type = $request->promotion_type;
        $promotion->appraisal_type = $request->appraisal_type;
        $promotion->promotion_reason = $request->promotion_reason;
        $promotion->department_id = $request->department_id;
        $promotion->designation_id = $request->designation_id;
        $promotion->band_id = $request->band_id;
        $promotion->amount = $request->amount;
        $promotion->brief_reason = $request->brief_reason;
        $promotion->location_id = $request->location_id;
        $promotion->created_by = auth()->user()->id;
        $promotion->updated_by = auth()->user()->id;
        $promotion->client_id = auth()->user()->cid;
        $promotion->created_by_role = $request->role;
        if ($promotion->save()) {
            if ($request->file('documents')) {
                foreach ($request->file('documents') as $file) {
                    $document = new Documents;
                    $path = Storage::disk('public')->put('documents', $file);
                    $document->url = $path;
                    $promotion->documents()->save($document);
                }
            }
            if (!isset($request->promotion_id)) {
                $data = MultiApprovalHelpers::enqueue_application_for_approval(auth()->user()->cid, 'promotion', $promotion->id, auth()->user()->id);
            }

            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue(auth()->user()->cid, 'promotion', auth()->user()->id, $request->role, $promotion->id);
            if ($isInQueue) {

                $param = "q.comments=" . "'" . "approved" . "'," . "q.status=" . "'" . "2" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
                $data = MultiApprovalHelpers::update_approval_queue(auth()->user()->cid, 'promotion', auth()->user()->id, $request->role, $promotion->id, $param);
            }
            // $approval_config = multiapprovalhelpers::get_approval_config('promotion', 48);
            // foreach ($approval_config as $config) {
            //     if ($config->role == $request->role) {

            //         $approvers_ids = multiapprovalhelpers::get_approvers_ids(56193, $config->role, $config->level_count, $config->module_id, 48, $config->role_id);
            //         foreach ($approvers_ids as $approver) {
            //             $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "2" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
            //             $data = MultiApprovalHelpers::update_approval_queue($promotion->client_id, 'promotion', $approver->empid, $request->role, $promotion->id, $param);
            //         }
            //     }
            // }

            //end flow

        } else {
            return response()->json([
                'success' => false,
                'message' => 'There is Some Issue!!',
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Promotion Add Successfully!!',
        ]);
    }

    public function resubmit_promotion(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pr_promotions,id',
            'emp_id' => 'required|exists:proll_employee,id',
            'promotion_type' => 'required|exists:pr_promotion_types,id',
            'appraisal_type' => 'required|exists:pr_appraisal_types,id',
            'promotion_reason' => 'required|exists:pr_promotion_reasons,id',
            'department_id' => 'required|exists:department_hierarchy,id',
            'designation_id' => 'required|exists:proll_client_designation,id',
            'band_id' => 'required|exists:employee_bands,id',
            'location_id' => 'required|exists:employee_bands,id',
            'amount' => 'required|integer',
            'brief_reason' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $promotion = Promotion::find($request->id);
        if (!empty($promotion)) {
            $promotion->emp_id = $request->emp_id;
            $promotion->promotion_type = $request->promotion_type;
            $promotion->appraisal_type = $request->appraisal_type;
            $promotion->promotion_reason = $request->promotion_reason;
            $promotion->department_id = $request->department_id;
            $promotion->designation_id = $request->designation_id;
            $promotion->band_id = $request->band_id;
            $promotion->amount = $request->amount;
            $promotion->brief_reason = $request->brief_reason;
            $promotion->location_id = $request->location_id;
            $promotion->updated_by = auth()->user()->id;
            $promotion->client_id = auth()->user()->cid;
            if ($promotion->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promotion Updated Successfully!!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'There is Some Issue!!',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Promotion not found against that id!!',
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:pr_promotions,id',
            'client_id' => 'required|exists:proll_client,id',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }

        $created_by = Promotion::where('id', $request->application_id)->pluck('created_by');
        if ($created_by[0] == auth()->user()->id) {
        multiapprovalhelpers::delete_application_from_approval_queue($request->client_id, 'promotion', $request->application_id);

        $promotion = Promotion::destroy($request->application_id);
        }
        if ($promotion) {
            return response()->json([
                'success' => true,
                'message' => 'Promotion withdraw Successfully!!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Not found',
        ]);
    }
}
