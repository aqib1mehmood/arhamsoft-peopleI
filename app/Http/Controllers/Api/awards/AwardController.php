<?php

namespace App\Http\Controllers\Api\awards;

use App\Helpers\MultiApprovalHelpers;
use App\Http\Controllers\Controller;
use App\Imports\AwardImport;
use App\Models\Award;
use App\Models\Documents;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class AwardController extends Controller
{
    public function latestAwardCount(Request $request)
    {
        $awards = DB::table('pr_awards')
            ->leftjoin('pr_award_types as types', 'types.id', 'pr_awards.award_type')
            ->leftjoin('proll_employee as clientID', 'clientID.id', 'pr_awards.emp_id')
            ->whereNull('pr_awards.deleted_at')
            ->where('pr_awards.award_status', '!=', 3)
            ->where('pr_awards.client_id', '=', $request->client_id)
            ->latest()->select('pr_awards.*', 'types.name as award_type', 'clientID.name')
            ->get()->unique('emp_id')->take(5);
        $allAwards = [];
        foreach ($awards as $index => $award) {
            $award->emp_id;
            $empAwards = DB::table('pr_awards')
                ->orderBy('created_at', 'desc')
                ->whereNull('deleted_at')
                ->where('award_status', '!=', 3)
                ->where('client_id', '=', $request->client_id)
                ->where('emp_id', '=', $award->emp_id)
                ->latest()
                ->get();
            $accept = 0;
            $pending = 0;
            $totalAmount = 0;
            foreach ($empAwards as $empAward) {
                $status = MultiApprovalHelpers::is_action_taken_on_application(auth()->user()->cid, 'award', $empAward->id);
                $totalAmount += $empAward->amount;
                if ($status == 0) {
                    $organizationCeo = DB::table('user_roles')->where(['role_id' => 5, 'cid' => auth()->user()->cid])->pluck('user_id')->first();
                    $ceoQueue = MultiApprovalHelpers::is_application_in_ceo_queue(auth()->user()->cid, 'award', $organizationCeo, 'CEO', $empAward->id);
                    if ($ceoQueue) {
                        $accept = $accept + 1;
                    } else {
                        $pending = $pending + 1;
                    }
                } else {
                    $pending = $pending + 1;
                }

            }
            $allAwards[$index]['approved'] = $accept;
            $allAwards[$index]['pending'] = $pending;
            $allAwards[$index]['totalAmount'] = $totalAmount;
            $allAwards[$index]['employee_name'] = $award->name;
            $allAwards[$index]['award_type'] = $award->award_type;
        }
        $award = [];
        foreach ($allAwards as $key => $value) {
            if ($value) {
                array_push($award, $value);

            }
        }
        return $award;
    }

    public function uploadCSV(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $array = Excel::toArray(new AwardImport, $request->file);
        if (count($array[0]) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'File should not be empty!!',
            ]);
        } else {
            $responseArray = [];
            foreach ($array as $arr) {
                foreach ($arr as $key => $nesArr) {
                    $input = [];
                    foreach ($nesArr as $index => $value) {
                        $input[$index] = $value;
                    }
                    $request = new Request($input);
                    // dd($request->all());
                    $storeResponse = $this->store($request);
                    if (!json_decode($storeResponse->getContent(), true)['success']) {
                        $responseArray[$key] = json_decode($storeResponse->getContent(), true)['message'];
                    }
                }
            }
            if (!empty($responseArray)) {
                return response()->json([
                    'success' => false,
                    'message' => $responseArray,
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!!',
            ]);
            // dd($responseArray);
        }
    }
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
        $awards = Award::leftjoin('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
            ->leftjoin('pr_award_types as types', 'types.id', 'pr_awards.award_type')
            ->leftjoin('proll_employee as create_by', 'create_by.id', 'pr_awards.created_by')
            ->leftjoin('proll_employee as update_by', 'update_by.id', 'pr_awards.updated_by')
            ->where('pr_awards.client_id', $request->client_id)
            ->orderBy('created_at', 'desc')
            ->select('pr_awards.*', 'emp.name as employee_name', 'types.name as award_type', 'create_by.name as create_by', 'update_by.name as update_by')
            ->selectRaw("CASE WHEN pr_awards.created_by  = $created_by THEN 'true' ELSE 'false' END AS award_owner_status")
            ->get();
        foreach ($awards as $award) {
            // dd($award);
            if ($award->award_status == 3) {
                $award->lm_status = $award->action_by_role == 2 ? 'Rejected' : 'Approved';
                $award->hr_status = $award->action_by_role == 3 ? 'Rejected' : 'Pending';
                $award->ceo_status = $award->action_by_role == 5 ? 'Rejected' : 'Pending';
            } else {
                $lm_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'award', $award->id, 2);
                $hr_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'award', $award->id, 3);
                $ceo_status = MultiApprovalHelpers::lm_hr_status_application(auth()->user()->cid, 'award', $award->id, 5);
                if ($lm_status == 0) {
                    $award->lm_status = 'Approved';
                } else {
                    $award->lm_status = 'Pending';
                }
                if ($hr_status == 0) {
                    $award->hr_status = 'Approved';
                } else {
                    $award->hr_status = 'Pending';
                }
                if ($ceo_status == 0) {
                    $award->ceo_status = 'Approved';
                } else {
                    $award->ceo_status = 'Pending';
                }
            }
            if ($award->issue_letter == 0) {
                $award->issue_letter = false;
            } else {
                $award->issue_letter = true;
            }
            // $Roles = Employee::User_Roles_v2_3($award->created_by, $award->client_id);
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

            $award->initiated_by = $award->created_by_role;
            $award->From = Carbon::createFromFormat('Y-m-d H:i:s', $award->created_at)->isoFormat('D-MMM-YYYY');
            $award->To = Carbon::createFromFormat('Y-m-d H:i:s', $award->updated_at)->isoFormat('D-MMM-YYYY');
        }
        // dd('ok');
        $count = $awards->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Awards Found' : 'No Award Found!',
            'results' => $count > 0 ? $awards : null,
        ]);
    }

    public function ceo_award_list(Request $request)
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
        $awards[] = '';
        $applications = DB::select(DB::raw("SELECT aq1.application_id,aq1.comments,aq1.role_id,p.name FROM `approval_queue` aq1 left join proll_employee p ON aq1.approver_empid=p.id WHERE (select count(*) from approval_queue aq2 where  module_id = 9 and  aq1.application_id=aq2.application_id and aq2.status != 2 ) = 0 group by aq1.application_id;"));
        // dd($applications);
        foreach ($applications as $index => $application) {
            $awards[$index] = Award::leftjoin('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
                ->leftjoin('pr_award_types as types', 'types.id', 'pr_awards.award_type')
                ->leftjoin('proll_employee as create_by', 'create_by.id', 'pr_awards.created_by')
                ->leftjoin('proll_employee as update_by', 'update_by.id', 'pr_awards.updated_by')
                ->where('pr_awards.client_id', $request->client_id)
                ->where('pr_awards.id', $application->application_id)
                ->where('pr_awards.award_status', '!=', 3)
                ->select('pr_awards.*', 'emp.name as employee_name', 'types.name as award_type', 'create_by.name as create_by', 'update_by.name as update_by')
                ->first();
// dump($application);

            if (!empty($awards[$index])) {
                // if($application->role_id == 3){
                // $awards[$index]->hr_coments = $application->comments .' - ' .$application->name;

                // }

                $awards[$index]->hr_status = 'Approved';
                $awards[$index]->approve_status = false;
                $Roles = Employee::User_Roles_v2_3($awards[$index]->created_by, $request->client_id);
                $awards[$index]->initiated_by = $Roles;
                $awards[$index]->From = Carbon::createFromFormat('Y-m-d H:i:s', $awards[$index]->created_at)->isoFormat('D-MMM-YYYY');
                $awards[$index]->To = Carbon::createFromFormat('Y-m-d H:i:s', $awards[$index]->updated_at)->isoFormat('D-MMM-YYYY');
                // $awards[$index]->toArray();
                // dump($awards);
            } else {
                // unset($awards[$index]);
            }

        }
        // dd($awards);

        $award = [];
        foreach ($awards as $key => $value) {
            if ($value) {
                array_push($award, $value);

            }
        }

        // $award = array_filter($awards, function ($value) {return !is_null($value) && $value !== '';});
        //  dd((array)$awards);
        $count = count($award);
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Awards Found' : 'No Award Found!',
            'results' => $count > 0 ? $award : null,
        ]);
    }
    public function get_recent_awards(Request $request)
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
        $awards = Award::leftjoin('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
            ->leftjoin('pr_award_types as types', 'types.id', 'pr_awards.award_type')
            ->groupBy('emp_id')
            ->where('pr_awards.client_id', $request->client_id)
            ->select('pr_awards.*', 'emp.name as employee_name', 'types.name as award_type')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->take(5)
            ->get();
        foreach ($awards as $award) {
            $Roles = Employee::User_Roles_v2_3($award->created_by, $award->client_id);
            $award->intiated_by = $Roles;
            $award->From = Carbon::createFromFormat('Y-m-d H:i:s', $award->created_at)->isoFormat('D-MMM-YYYY');
            $award->To = Carbon::createFromFormat('Y-m-d H:i:s', $award->updated_at)->isoFormat('D-MMM-YYYY');
        }
        $count = $awards->count();
        $allAwards = $this->latestAwardCount($request);
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Awards Found' : 'No Award Found!',
            'results' => $count > 0 ? $awards : null,
            'award_status_result' => $allAwards,
        ]);
    }

    public function get_award_via_employee(Request $request)
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
        $award = Award::join('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
            ->join('pr_award_types as types', 'types.id', 'pr_awards.award_type')
            ->join('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->join('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->where('emp_id', $request->emp_id)
            ->select('pr_awards.*', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'types.name as award_type')
            ->get();
        $count = $award->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Award Found' : 'No Award Found!',
            'results' => $count > 0 ? $award : null,
        ]);
    }

    public function show_award(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pr_awards,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $created_by = auth()->user()->id;
        $award = Award::leftjoin('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
            ->leftjoin('pr_award_types as types', 'types.id', 'pr_awards.award_type')
            ->leftjoin('department_hierarchy as depart', 'depart.id', 'emp.department_id')
            ->leftjoin('proll_client_designation as design', 'design.designation_id', 'emp.designation')
            ->leftjoin('approval_queue as q', 'q.application_id', 'pr_awards.id')
            ->leftjoin('approval_queue as q2', 'q2.application_id', 'pr_awards.id')
            ->leftjoin('proll_employee as e', 'e.id', 'q.approver_empid')
            ->leftjoin('proll_employee as e2', 'e2.id', 'q2.approver_empid')
            ->where('q.role_id', 3)
            ->where('q2.role_id', 2)
            ->where('q.module_id', 9)
            ->where('pr_awards.id', $request->id)
            ->select('pr_awards.*', 'emp.name as employee_name', 'depart.department_name', 'design.designation_name', 'types.name as award_types', 'q.comments as hr_comment', 'e.name as hr_name', 'q2.comments as lm_comment', 'e2.name as lm_name')
            ->selectRaw("CASE WHEN year_type = 1 THEN 'Fiscal Year' ELSE 'Quarter Year' END AS year_type")
            ->selectRaw("CASE WHEN pr_awards.created_by  = $created_by THEN 'true' ELSE 'false' END AS withdraw_status")
            ->orderBy('q2.id', 'DESC')
            ->first();
        $count = 0;
        if ($award) {
            $count = $award->count();
        }
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Award Found' : 'This Application is Rejected!',
            'results' => $count > 0 ? [$award] : null,
        ]);
    }

    public function show_award_attachments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pr_awards,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $award_attachment = Award::find($request->id);
        if (!empty($award_attachment)) {
            $award_attachment = $award_attachment->documents;
            foreach ($award_attachment as $data) {
                $data->url = env('APP_URL') . '/backend/storage/app/public/' . $data->url;

            }
            $count = $award_attachment->count();
            return response()->json([
                'success' => $count > 0 ? true : false,
                'message' => $count > 0 ? 'Attachment Found' : 'No Attachment Found!',
                'results' => $count > 0 ? $award_attachment : null,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No Attachment Found!',
                'results' => null,
            ]);
        }

    }

    public function change_award_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:pr_awards,id',
            'client_id' => 'required|exists:proll_client,id',
            'role' => 'required|in:LM,HR,CEO',
            'status' => 'required|in:2,3,4',
            // 'approver_id' => 'required',
            'comment' => 'required',
        ]);

        //Lm 55573
        //HR 58499
        //CEO 17282

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
                $data = MultiApprovalHelpers::reset_approval_queue($request->client_id, 'award', $request->application_id);
                $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . $request->status . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
                $status = MultiApprovalHelpers::update_approval_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id, $param);
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
                            $approvers['status'] = 1;
                            DB::table('approval_queue')
                                ->insert($approvers);
                        }
                    }

                }

            }
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id);

            if ($isInQueue) {
                $data = multiapprovalhelpers::delete_application_from_approval_queue($request->client_id, 'award', $request->application_id);
                $award = Award::find($request->application_id);
                $award->award_status = $request->status;
                $award->resubmit_status = 1;
                $award->updated_by = auth()->user()->id;
                $award->action_by_role = $action_by_role;
                $award->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Status Change Sucessfully!!',
                ]);
            }
        }
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
                        $approvers['status'] = 2;
                        DB::table('approval_queue')
                            ->insert($approvers);
                    }
                }

            }

        }
        $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "$request->status" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
        // $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id, $param);
        $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'award', auth()->user()->id, $request->role, $request->application_id, $param);
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

    public function change_multiple_award_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id.*' => 'required|exists:pr_awards,id',
            'client_id' => 'required|exists:proll_client,id',
            'role' => 'required',
            'status' => 'required|in:2,3,4',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $ids = $request->application_id;
        // dd($ids);
        // $applications = explode(",", $ids[0]);
        foreach ($ids as $application) {
            $approval_config = multiapprovalhelpers::get_approval_config('award', $request->client_id);

            foreach ($approval_config as $config) {
                // dump($config);
                $priority = 1;

                if ($config->role == 'CEO') {
                    $approvers = array(
                        'comments' => $request->comment,
                    );
                    $module_id = $config->module_id;
                    $approvalQueue = DB::table('approval_queue')->where(['cid' => $request->client_id, 'approval_type' => $config->approval_type, 'role_id' => $config->role_id, 'approver_empid' => auth()->user()->id, 'application_id' => $application, 'module_id' => $config->module_id])->first();
                    // dd($approvalQueue);
                    if ($approvalQueue) {
                        DB::table('approval_queue')->where('id', $approvalQueue->id)
                            ->update($approvers);

                    } else {
                        $approvers['cid'] = $request->client_id;
                        $approvers['approval_type'] = $config->approval_type;
                        $approvers['role_id'] = $config->role_id;
                        $approvers['approver_empid'] = auth()->user()->id;
                        $approvers['application_id'] = $application;
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

            $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "$request->status" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
            $data = MultiApprovalHelpers::update_approval_queue($request->client_id, 'award', auth()->user()->id, $request->role, $application, $param);

        }
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

    public function award_application_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emp_id' => 'nullable|exists:pr_awards,emp_id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $accept = 0;
        $accept_amount = 0;
        $reject = 0;
        $reject_amount = 0;
        $pending = 0;
        $pending_amount = 0;
        $awards = Award::where('created_by', auth()->user()->id)->where('client_id', $request->client_id);
        if ($request->emp_id) {
            $awards->where('emp_id', $request->emp_id);
        }
        $awards = $awards->get();
        if ($awards) {
            $total_application = '';
            $total_amount = '';
            foreach ($awards as $award) {
                if ($award->award_status == 3) {
                    $reject = $reject + 1;
                    $reject_amount = $reject_amount + $award->amount;
                } else {
                    $status = MultiApprovalHelpers::is_action_taken_on_application(auth()->user()->cid, 'award', $award->id);
                    if ($status == 0) {
                        $organizationCeo = DB::table('user_roles')->where(['role_id' => 5, 'cid' => auth()->user()->cid])->pluck('user_id')->first();
                        $ceoQueue = MultiApprovalHelpers::is_application_in_ceo_queue(auth()->user()->cid, 'award', $organizationCeo, 'CEO', $award->id);
                        if ($ceoQueue) {
                            $accept = $accept + 1;
                            $accept_amount = $accept_amount + $award->amount;
                        } else {
                            $pending = $pending + 1;
                            $pending_amount = $pending_amount + $award->amount;
                        }

                    } else {
                        $pending = $pending + 1;
                        $pending_amount = $pending_amount + $award->amount;
                    }
                }

                $total_application = $accept + $reject + $pending;
                $total_amount = $accept_amount + $reject_amount + $pending_amount;
            }
            return response()->json([
                'success' => true,
                'message' => 'Award Found',
                'approve_award' => $accept > 0 ? $accept : null,
                'reject_award' => $reject > 0 ? $reject : null,
                'pendding_award' => $pending > 0 ? $pending : null,
                'total_award' => $total_application > 0 ? $total_application : null,
                'approve_amount' => $accept_amount > 0 ? $accept_amount : null,
                'reject_amount' => $reject_amount > 0 ? $reject_amount : null,
                'pendding_amount' => $pending_amount > 0 ? $pending_amount : null,
                'total_amount' => $total_amount > 0 ? $total_amount : null,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'not found',
        ]);

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
            'award_type' => 'required|exists:pr_award_types,id',
            'amount' => 'required|integer',
            'fiscal_year' => 'required|date_format:d/m/Y',
            'brief_reason' => 'required',
            'role' => 'required|in:LM,HR',
            'year_type' => 'nullable|in:0,1',
            'award_id' => 'nullable|exists:pr_awards,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        if (isset($request->award_id)) {
            $award = Award::find($request->award_id);
        } else {
            $award = new Award;
        }
        $award->emp_id = $request->emp_id;
        $award->award_type = $request->award_type;
        $award->amount = $request->amount;
        $date = Carbon::createFromFormat('d/m/Y', $request->fiscal_year)->format('Y-m-d');
        $award->fiscal_year = $date;
        $award->created_by = auth()->user()->id;
        $award->updated_by = auth()->user()->id;
        $award->client_id = auth()->user()->cid;
        $award->brief_reason = $request->brief_reason;
        $award->year_type = $request->year_type;
        $award->created_by_role = $request->role;
        if ($award->save()) {
            if ($request->file('documents')) {
                foreach ($request->file('documents') as $file) {
                    $document = new Documents;
                    $path = Storage::disk('public')->put('documents', $file);
                    $document->url = $path;
                    $award->documents()->save($document);
                }
            }

            if (!isset($request->award_id)) {
                $data = MultiApprovalHelpers::enqueue_application_for_approval(auth()->user()->cid, 'award', $award->id, auth()->user()->id);
            }
            $isInQueue = MultiApprovalHelpers::is_application_in_ceo_queue(auth()->user()->cid, 'award', auth()->user()->id, $request->role, $award->id);
            if ($isInQueue) {

                $param = "q.comments=" . "'" . "approved" . "'," . "q.status=" . "'" . "2" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
                $data = MultiApprovalHelpers::update_approval_queue(auth()->user()->cid, 'award', auth()->user()->id, $request->role, $award->id, $param);
            }
            //just flow testing

            // $approval_config = multiapprovalhelpers::get_approval_config('award', auth()->user()->cid);
            // foreach ($approval_config as $config) {
            //     if ($config->role == $request->role) {

            //         $approvers_ids = multiapprovalhelpers::get_approvers_ids(auth()->user()->id, $config->role, $config->level_count, $config->module_id, auth()->user()->cid, $config->role_id);
            //         foreach ($approvers_ids as $approver) {
            //             $param = "q.comments=" . "'" . "$request->comment" . "'," . "q.status=" . "'" . "2" . "'," . "q.approver_view=" . "'" . "1" . "'," . "q.emp_view=" . "'" . "1" . "'";
            //             $data = MultiApprovalHelpers::update_approval_queue($award->client_id, 'award', $approver->empid, $request->role, $award->id, $param);
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
            'message' => 'Award Add Successfully!!',
        ]);
    }

    public function resubmit_award(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'emp_id' => 'required|exists:proll_employee,id',
            'award_type' => 'required|exists:pr_award_types,id',
            'amount' => 'required|integer',
            'fiscal_year' => 'required|date_format:d/m/Y',
            'brief_reason' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $award = Award::find($request->id);
        if (!empty($award)) {
            $award->emp_id = $request->emp_id;
            $award->award_type = $request->award_type;
            $award->amount = $request->amount;
            $date = Carbon::createFromFormat('d/m/Y', $request->fiscal_year)->format('Y-m-d');
            $award->fiscal_year = $date;
            $award->updated_by = auth()->user()->id;
            $award->client_id = auth()->user()->cid;
            $award->brief_reason = $request->brief_reason;
            if ($award->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Award updated Successfully!!',
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
                'message' => 'There is award against that id!!',
            ]);
        }

    }
    public function has_issue_letter(Request $request)
    {
        $ids = $request->ids;
        $awards = explode(",", $ids[0]);
        foreach ($awards as $award) {
            $award = Award::where('id', '=', $award)->first();
            $award->issue_letter = 1;
            $award->updated_by = auth()->user()->id;
            $award->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'Issue Letter Done!!',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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
            'application_id' => 'required|exists:pr_awards,id',
            'client_id' => 'required|exists:proll_client,id',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }

        $award = false;
        $created_by = Award::where('id', $request->application_id)->pluck('created_by');
        if ($created_by[0] == auth()->user()->id) {
            multiapprovalhelpers::delete_application_from_approval_queue($request->client_id, 'award', $request->application_id);
            $award = Award::destroy($request->application_id);
        }
        if ($award) {
            return response()->json([
                'success' => true,
                'message' => 'Award withdraw Successfully!!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Access denied',
        ]);
    }
}
