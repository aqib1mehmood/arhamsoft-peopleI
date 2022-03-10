<?php


namespace App\Helpers;

use App\Models\ApplicationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class multiapprovalhelpers
{
    public  static function  get_approval_config($module,$cid) {

        return db::table('approval_config as ac')
            ->leftjoin('modules as m','ac.module_id','=','m.id')
            ->leftjoin('roles as r','ac.role_id','=','r.id')
            ->where('m.name','=',$module)
            ->where('ac.status','=','1')
            ->where('ac.cid','=',$cid)
            ->select()
            ->get();
            }

    public static function  get_approvers_ids($empid, $role, $level,$module_id,$cid,$role_id) {
        $approver_ids = array();
        $applicant_id=$empid;
        if (strtolower($role) == "lm") {
            $approval_seq=self::getreportingsequenctbymodule($module_id,$cid,$role_id);
            $first_approval = $second_approval =$third_approval = '';
            $sql = '';
            $result = array();
            $reporting_count = 0;
            if($approval_seq){
                $lm_labels=array('dept_id'=>'deparment lm','reporting_to_id'=>'external lm','second_reporting_to_id'=>'external lm');
                foreach ($approval_seq as $seq) {
                    if($reporting_count<$level){
                        $result[$seq] =  $seq;

                        $res=db::select(db::raw("select '".$lm_labels[$seq]."' role, m.empid,m.id approver_id,m.parent_reporting_lm,m.line_manager approver_name,d.designation_name,dmh.department_name
                        from proll_department_managers m
                        left join  proll_employee e on e.$seq=m.id
                        left join department_hierarchy dh on dh.id=m.department_hierarchy_id
                        left join proll_employee em on em.id=m.empid
                        left join proll_client_designation d on em.designation=d.designation_id
                        left join department_hierarchy dmh on dmh.id=m.department_hierarchy_id
                        where e.id=$empid and m.empid!=$empid"));

                        $result[$seq] =  $res;
                        $reporting_count++;
                    }
                }
            }
            $count = count($approval_seq);
            $approver_ids = $result['dept_id'];
            while ($count<$level) {
                $response=db::select(db::raw("select 'deparment lm' role,m1.empid,m1.id as approver_id,m1.parent_reporting_lm,m1.line_manager approver_name,d.designation_name,dmh.department_name
                            from proll_employee e
                            left join proll_department_managers m on e.dept_id=m.id
                            left join proll_department_managers m1 on m1.id=m.parent_reporting_lm
                            left join department_hierarchy dh on dh.id=m.department_hierarchy_id
                            left join proll_employee em on em.id=m1.empid
                            left join proll_client_designation d on d.designation_id=em.designation
                            left join department_hierarchy dmh on dmh.id=m1.department_hierarchy_id
                            where e.id='$empid';"));
                if($response) {
                    $empid = $response[0]->empid;
                }
                if(empty($empid)){
                    break;
                }
                $count_approval = 0;
                $temp_array=array();
                foreach ($approver_ids as $approver_id) {
                    array_push($temp_array, $approver_id->empid);
                }
                if(!in_array($empid, $temp_array)){
                    array_push($approver_ids,$response);
                }
                if($response[0]->parent_reporting_lm==0){
                    break;
                }
                $level--;
            }
            $sql='';
            $result['dept_id']=$approver_ids;
            $approver_ids = multiapprovalhelpers::remove_array_keys($result);
        }elseif (strtolower($role) == "hr") {
            $data=multiapprovalhelpers::get_approvers_by_module_and_role($module_id,$role_id,$applicant_id);
            foreach($data as $info){
                array_push($approver_ids,$info);
            }
        }
        return $approver_ids;

    }
    /**
     *
     * @param type $data
     * @return type
     */
    public static function  count_value_of_array($data){
        $count = 0;
        foreach ($data as $value) {
            if(is_array($value)) {
                $count += count($value);
            }
        }
        return $count;
    }
    /**
     * provide associated array and get all array without index s
     * @param type $associated_array
     * @return array
     */
    public static function  remove_array_keys($associated_array){
        $data = array();
        foreach ($associated_array as $key => $line_manager_array) {
            if(is_array($line_manager_array)) {
                foreach ($line_manager_array as $value) {
                    if (!empty($value)) {
                        array_push($data, $value);
                    }
                }
            }
        }
        return $data;
    }
    public static function  enqueue_application_for_approval($cid, $module, $application_id, $empid,$comments="applied") {
        $approval_config = multiapprovalhelpers::get_approval_config($module,$cid);
        $approvers=array();
        $module_id=0;
        foreach ($approval_config as $config) {
            $priority = 1;
            $approvers_ids = multiapprovalhelpers::get_approvers_ids($empid, $config->role, $config->level_count,$config->module_id,$cid,$config->role_id);

            foreach ($approvers_ids as $approver) {
                $approvers[] = array(
                    'cid' => $cid,
                    'approval_type' => $config->approval_type,
                    'role_id' => $config->role_id,
                    'approver_id' => $approver->approver_id,
                    'approver_empid' => $approver->empid,
                    'module_id' => $config->module_id,
                    'application_id' => $application_id,
                    'priority' => $priority,
                    'max_count' => count($approvers_ids),
                    'created_at' => date('y-m-d h:i:s'),
                    'status' => 1
                );
                if($config->approval_type=='sequential'){
                    $priority++;
                }
                $module_id=$config->module_id;
            }
        }
        foreach ($approvers as $data) {
            db::table('approval_queue')
                ->insert($data);
        }

        self::addapplicationlog($cid,$module_id,$role_id=1,$empid,$application_id,$status=1,$comments);
    }

    public static function  get_application_approvel_detail($cid, $module, $application_id, $role='') {
        $res= DB::table('approval_queue as q')
            ->leftJoin('modules as m','q.module_id','=','m.id')
            ->leftJoin('roles as r','q.role_id','=','r.id')
            ->leftJoin('proll_employee as e','q.approver_empid','=','e.id')
            ->leftJoin('proll_client_designation as d','d.designation_id','=','e.designation')
            ->where('q.module_id','=',$module)
            ->where('q.cid','=',$cid)
            ->where('q.application_id','=',$application_id)
            ->where('r.role','like','%'.$role.'%')
            ->select('r.role','e.name',
                    DB::raw('(CASE WHEN e.picture IS NOT NULL THEN  CONCAT("'.env('BASE_URL').'/emp_pictures/",e.picture) ELSE "'.env('BASE_URL').'/emp_pictures/favicon.png" END) AS profile'),
                    'd.designation_name',
                    DB::raw("(case when q.`status`=1 then 'Pending'
                        when q.`status`=2 then 'Approved'
                        when q.`status`=3 then 'Disapproved'
                        when q.`status`=4 then 'Resubmit' END) `status`"),
                    'q.comments', 'q.created_at', 'q.updated_at','q.actioned_at')
            ->get();
        // IF application resubmit/ disapproved by any approver then status for rest of the approvers will be N/A
        $data=array();$status='';
        foreach ($res as $row){
            if(!empty($status)){
                $row->status=$status ;
            }
            if($row->status=='Disapproved' || $row->status=='Resubmit'){
                $status='N/A';
            }
            $data[]=$row;
        }
        return $data;

    }

    public static function  delete_application_from_approval_queue($cid, $module, $application_id) {
        return DB::table('approval_queue as q')
            ->join('modules as m','q.module_id','=','m.id')
            ->where('q.cid','=',$cid)
            ->where('m.name','=',$module)
            ->where('q.application_id','=',$application_id)
            ->delete();
    }

    public static function  get_in_my_queue_applications($cid, $module, $role, $approver_id,$view_status='',$status='1') {
        if($status=='1') {
            $res = DB::select(DB::raw("SELECT GROUP_CONCAT(q.application_id) AS ids
                            FROM approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            WHERE
                            (SELECT COUNT(q2.id) FROM approval_queue q2 WHERE q2.`status` IN (3,4) AND q.application_id=q2.application_id AND q.module_id=q2.module_id) < 1 AND
                            (q.cid='$cid' AND m.name='$module' AND r.role='$role' AND q.approver_empid='$approver_id' AND q.approver_view LIKE '%$view_status%' AND q.`status`=1)
                            AND
                            (
                            (q.approval_type='sequential' AND (q.priority=1 OR q.application_id IN (
                                    SELECT q1.application_id FROM approval_queue q1 WHERE
                                    (SELECT COUNT(q2.id) FROM approval_queue q2 WHERE q2.`status` IN (3,4) AND q1.application_id=q2.application_id AND q1.module_id=q2.module_id) < 1
                                    AND q1.priority=q.priority-1 AND q1.`status`='2' AND q1.role_id=q.role_id AND q1.module_id=q.module_id))
                            ) OR (
                                            q.approval_type='parallel' AND (
                                            q.application_id IN (
                                            SELECT q1.application_id
                                            FROM approval_queue q1
                                            WHERE
                                            (SELECT COUNT(q2.id) FROM approval_queue q2 WHERE q2.`status` IN (3,4) AND q1.application_id=q2.application_id AND q1.module_id=q2.module_id) < 1
                                            AND q1.role_id=q.role_id)
                                            )
                                    )
                            )
                            "));
            return $res[0]->ids;
        }else{
           return self::get_application_ids_by_queue_status($cid,$module,$role,$approver_id,$status);
        }
    }
    public static function  get_application_ids_by_approval_status($cid, $module, $role, $approver_id, $status) {
        $res= DB::select(DB::raw("SELECT GROUP_CONCAT(q.application_id) AS ids
                            FROM approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            WHERE
                            q.cid='$cid' AND m.name='$module' AND r.role='$role' AND q.approver_empid='$approver_id' AND q.status=$status"));
        return $res[0]->ids;
    }
    public static function  get_all_hr_view_applications_branch_wise($selecttion,$cid, $module, $role, $approver_id,$configuration_type=''){
        return DB::table('approval_config_department as b')
            ->leftJoin('approval_role_config as c','c.id','=','b.approval_role_config_id')
            ->leftJoin('approval_config as ac','ac.id','=','c.approval_config_id')
            ->leftJoin('modules as m','ac.module_id','=','m.id')
            ->leftJoin('roles as r','ac.role_id','=','r.id')
            ->where('c.assist_id','=',$approver_id)
            ->where('c.cid','=',$cid)
            ->where('m.name','=',$module)
            ->where('r.role','=',$role)
            ->select(DB::raw('GROUP_CONCAT('.$selecttion.') ids'))
            ->get();
    }
    public static function  get_all_hr_view_applications_department_wise($cid, $module, $role, $approver_id,$configuration_type=''){
        return DB::table('approval_config_department as b')
            ->leftJoin('approval_role_config as c','c.id','=','b.approval_role_config_id')
            ->leftJoin('approval_config as ac','ac.id','=','c.approval_config_id')
            ->leftJoin('modules as m','ac.module_id','=','m.id')
            ->leftJoin('roles as r','ac.role_id','=','r.id')
            ->where('c.assist_id','=',$approver_id)
            ->where('c.cid','=',$cid)
            ->where('m.name','=',$module)
            ->where('r.role','=',$role)
            ->select(DB::raw('GROUP_CONCAT(b.department_id) ids'))
            ->get();


    }
    public static function  get_approval_count($cid, $module,$application_id, $role="") {
        return DB::table('approval_queue as q')
            ->leftJoin('roles as r','q.role_id','=','r.id')
            ->leftJoin('modules as m','q.module_id','=','m.id')
            ->where('q.cid','=',$cid)
            ->where('m.name','=',$module)
            ->where('r.role','like','%'.$role.'%')
            ->where('q.application_id','=',$application_id)
            ->count();
    }
    public static function  get_approved_count($cid, $module,$application_id, $role="") {
        return DB::table('approval_queue as q')
            ->leftJoin('roles as r','q.role_id','=','r.id')
            ->leftJoin('modules as m','q.module_id','=','m.id')
            ->where('q.cid','=',$cid)
            ->where('m.name','=',$module)
            ->where('r.role','like','%'.$role.'%')
            ->where('q.application_id','=',$application_id)
            // ->where('q.status','=',2)
            ->count();
    }
    public static function  update_approval_queue($cid, $module,$approver_id,$role,$application_id,$update_param) {
        return DB::update(DB::raw("UPDATE approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            SET $update_param
                            WHERE q.cid='$cid' AND m.name='$module'
                            AND r.role LIKE '%$role%' AND q.application_id='$application_id'
                            AND q.approver_empid='$approver_id';"));
    }

    public static function  is_application_in_my_queue($cid, $module,$approver_id,$role,$application_id) {

        $res= DB::select(DB::raw("SELECT COUNT(q.application_id) as in_my_queue
                            FROM approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            WHERE q.cid='$cid' AND m.name='$module'
                            AND r.role='$role' AND q.approver_empid='$approver_id' AND q.application_id='$application_id'
                            AND q.`status`=1 AND (q.priority=1 OR q.application_id IN (
                            SELECT q1.application_id
                            FROM approval_queue q1
                            WHERE q1.priority=q.priority-1 AND q1.`status`='2' AND q1.role_id=q.role_id AND q1.module_id=q.module_id
                            ));"));
        return ($res[0]->in_my_queue?true:false);
    }
    public static function  get_applications_by_status($cid, $module,$module_tbl,$module_pkey,$approval_status,$where, $role="") {
        return DB::select(DB::raw("SELECT q.application_id,e.name
                            FROM approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            LEFT JOIN proll_employee e ON q.approver_empid=e.id
                            LEFT JOIN $module_tbl ON q.application_id= $module_tbl.$module_pkey
                            WHERE q.cid='$cid' AND m.name='$module' AND r.role like '%$role%'
                            AND q.emp_view=0 AND q.`status`='$approval_status' $where  GROUP BY q.application_id;;"));
    }
    public static function  update_approval_view_status($cid, $module,$role,$application_id,$approver_id,$update_param) {
        return DB::update(DB::raw("UPDATE approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            SET $update_param
                            WHERE q.cid='$cid' AND m.name='$module' AND r.role LIKE '%$role%' AND q.approver_empid='$approver_id'
                            AND  q.application_id='$application_id';"));
    }
    public static function  reset_approval_queue($cid, $module,$application_id) {
        return DB::update(DB::raw("UPDATE approval_queue q
                            LEFT JOIN roles r ON q.role_id=r.id
                            LEFT JOIN modules m ON q.module_id=m.id
                            SET q.`status`=1,q.comments='',q.approver_view=0,q.actioned_at=null
                            WHERE q.cid='$cid' AND m.name='$module'
                            AND  q.application_id='$application_id';"));
    }

    public static function  is_action_taken_on_application($cid, $module,$application_id) {

        $res=DB::select(DB::raw("SELECT COUNT(q.application_id) AS pending_approval_count, (
                        SELECT COUNT(q.application_id)
                        FROM approval_queue q
                        LEFT JOIN modules m ON q.module_id=m.id
                        WHERE q.cid='$cid' AND m.name='$module' AND q.application_id='$application_id') AS total
                        FROM approval_queue q
                        LEFT JOIN modules m ON q.module_id=m.id
                        WHERE q.cid='$cid' AND m.name='$module' AND q.application_id='$application_id' AND q.`status`=1;"));

        if($res && $res[0]->total==$res[0]->pending_approval_count){
            return 0;
        }else{
            return 1;
        }
    }

    public static function  get_manager_detail($lm_id,$cid){
        return DB::table('proll_department_managers')
            ->where('cid','=',$cid)
            ->where('id','=',$lm_id)
            ->select(DB::raw('*'))
            ->get();
    }

    public static function  get_approvers_by_module_and_role($module_id,$role_id,$applicant_id){
        $sql="SELECT configuration_type FROM approval_config WHERE module_id='$module_id' AND role_id='$role_id'";
        $config_type=  DB::table('approval_config')
                            ->where('module_id',$module_id)
                            ->where('role_id',$role_id)
                            ->select('configuration_type')->first();
        if($config_type && $config_type->configuration_type=='branch'){
            $branch=  MultiApprovalHelpers::get_emp_branch($applicant_id);
            $branch_id=$branch->branch_id;
            $append=" AND cd.branch_id='$branch_id'";
        }else{
            $department=MultiApprovalHelpers::get_emp_department($applicant_id);
            $department_id=$department->department_hierarchy_id;

            $append=" AND cd.department_id='$department_id'";
        }


        return DB::select(DB::raw("SELECT s.empid,s.assist_id approver_id ,e.name approver_name,d.designation_name,dh.department_name
        FROM approval_sequence s
        LEFT JOIN approval_config_detail cd ON s.config_detail_id=cd.id
        LEFT JOIN approval_config c ON cd.approval_config_id=c.id
        LEFT JOIN proll_employee e ON e.id=s.empid
        LEFT JOIN proll_client_designation d ON e.designation=d.designation_id
        LEFT JOIN proll_department_managers m ON e.dept_id= m.id
        LEFT JOIN department_hierarchy dh ON dh.id=m.department_hierarchy_id
        WHERE c.module_id='$module_id'
        AND c.role_id='$role_id'$append ORDER BY s.approval_priority"));
    }
    public static function  get_emp_branch($empid){
        return DB::table('proll_employee as e')
            ->leftJoin('proll_client_location as l','e.loc_id','=','l.loc_id')
            ->where('e.id','=',$empid)
            ->select('l.loc_id as branch_id','l.loc_desc')
            ->first();
    }
    public static function  get_emp_department($empid){
        return DB::table('proll_employee as e')
            ->leftJoin('proll_department_managers as m','e.dept_id','=','m.id')
            ->where('e.id','=',$empid)
            ->select('m.department_hierarchy_id')
            ->first();
    }
    public static function  get_modules(){
        return DB::table('modules')
            ->where('status','=','1')
            ->select(DB::raw('*'))->get();

    }
    public static function get_all_reporting_employees_with_all_columns($user_id,$department_manager_id,$keys=NULL,$status=NULL,$search_by=NULL,$sort_by=' e.name ',$start_limit=0,$end_limit=0,$direct_report=NULL,$level=NULL)
    {

       $all_departments =array();
        if (true) {
            // get current department by linemanger id
           $departments = DB::table('proll_department_managers')
                ->where('id','=',$department_manager_id)
                ->select('department_hierarchy_id')->get();
           foreach ($departments as $department) {
           // get all sub departments including self
           if(!empty($department->department_hierarchy_id)){

               // pass level in second param where 0 means direct report and 1 means 2 level
                    $reporting_departments_string = MultiApprovalHelpers::get_all_sub_departments_by_department_id($department->department_hierarchy_id,$level);

                if(!empty($reporting_departments_string)){
                    array_push($reporting_departments_string, $department->department_hierarchy_id);
                }else{
                    array_push($reporting_departments_string, $department->department_hierarchy_id);
                }
                $reporting_departments = MultiApprovalHelpers::get_department_details_by_ids($reporting_departments_string);
            }
        }

            if (!empty($reporting_departments)) {
                foreach ($reporting_departments as $reporting_department) {
                    array_push($all_departments, $reporting_department->id);
            }
            $reporting_department =array();
            if(empty($status)){
                $status="AND e.`status`=1";
            }
            $limit = '';
            if($end_limit!=0){
                $limit = " LIMIT {$start_limit},$end_limit";
            }
          if($direct_report=='selected'){
              $direct_report = " AND ((e.dept_id='$department_manager_id' AND e.reporting_to_id=0 AND e.second_reporting_to_id=0) OR (e.reporting_to_id='$department_manager_id') OR (e.second_reporting_to_id='$department_manager_id'))";
          }else{
               $direct_report = " AND (d.department_hierarchy_id IN  (".implode(',', $all_departments).") OR ((e.dept_id='$department_manager_id' AND e.reporting_to_id=0 AND e.second_reporting_to_id=0) OR (e.reporting_to_id='$department_manager_id') OR (e.second_reporting_to_id='$department_manager_id')) )";
          }
                return DB::select(DB::raw("SELECT e.id,e.name,e.f_hname,e.cnic,e.doj,
                                            desig.designation_name AS designation,dh.department_name as department,e.hom_address,
                                            e.hom_phone,e.cell_number,e.status,ed.employeeno  AS empcode,
                                            ed.employeeno,e.name_salute,e.date_of_rejection,e.dept_id
                                            FROM proll_department_managers d
                                            LEFT JOIN department_hierarchy dh ON d.department_hierarchy_id=dh.id
                                            LEFT JOIN proll_employee e ON e.dept_id=d.id
                                            LEFT JOIN proll_employee_detail ed ON ed.empid = e.id
                                            LEFT JOIN proll_client_designation desig ON desig.designation_id = e.designation
                                            WHERE  e.id<>'" . $user_id . "'  $keys  $status $search_by
                                            $direct_report
                                            GROUP BY e.id
                                            ORDER BY $sort_by  $limit"));

        }
        return FALSE;
    } else {
        return FALSE;
    }
}
/**
 * @param type $department_id
 * @return boolean
 * Get all sub department list
 */
 public static function get_all_sub_departments_by_department_id($department_id, $level = 100) {
    if (empty($level)) {
        $level = 100;
    }
    if (true) {
         $department_list=DB::select(DB::raw("SELECT `GetAllSubDepartmentsByDepartment`($department_id,$level) as sub_departments"));
        if (!empty($department_list[0]->sub_departments)) {
            return explode(',', $department_list[0]->sub_departments);
        } else {
            return array();
        }
    } else {
        return FALSE;
    }
}

 public static function get_department_details_by_ids($ids) {
    return DB::table('department_hierarchy')
            ->whereIn('id', $ids)
            ->select('id','department_name','reporting_department_id AS parent_id','country_id','status')->get();

}
 public static function get_lm_id($user_id) {
    return DB::table('proll_department_managers')
                ->where('empid','=',$user_id)
                ->select('id')->first();

}

public static function getReportingSequenctByModule($module_id,$cid,$role_id){
     $reporting_column_sequence= DB::table('approval_config')
         ->where('module_id',$module_id)
         ->where('cid',$cid)
         ->where('role_id',$role_id)
         ->pluck('reporting_column_sequence')->first();
     if($reporting_column_sequence){
         return explode(',',$reporting_column_sequence);
     }else{
         return [];
     }
}

    public static function addApplicationLog($cid,$module_id,$role_id,$user_id,$application_id,$status,$comments)
    {
        ApplicationLog::create([
            'cid'=>$cid,
            'user_id'=>$user_id,
            'role_id'=>$role_id,
            'module_id'=>$module_id,
            'application_id'=>$application_id,
            'status'=>$status,
            'comments'=>$comments
        ]);
    }

    public static function getapplicationlog($module_id,$application_id)
    {

       return db::table('application_log as a')
            ->leftjoin('proll_employee as e','a.user_id','=','e.id')
            ->leftjoin('proll_client_designation as desig','e.designation','=','desig.designation_id')
            ->leftjoin('department_hierarchy as dept','e.department_id','=','dept.id')
            ->leftjoin('roles as r','a.role_id','=','r.id')
            ->leftjoin('proll_reference_data as dr','a.status','=','dr.reference_key')
               ->leftjoin('proll_reference_data_code as d','d.ref_id', '=', 'dr.ref_id')
           ->where('d.reference_code','=','application_status')
            ->where('a.application_id',$application_id)
            ->where('a.module_id','=',$module_id)
            ->select('a.user_id','e.name','dept.department_name','desig.designation_name',
                db::raw('(case when e.picture is not null then  concat("'.env('BASE_URL').'/emp_pictures/",e.picture) else "'.env('BASE_URL').'/emp_pictures/favicon.png" end) as profile'),
                'r.name as role','dr.description as status','a.comments','a.updated_at as actioned_at')
            ->get();
    }

    public static function get_app_count_by_status($cid,$module,$role,$approver_id,$status){
     return $res=DB::table('approval_queue as q')
     ->leftJoin('roles as r','q.role_id','=','r.id')
     ->leftJoin('modules as m','q.module_id','=','m.id')
     ->where('q.cid','=',$cid)
     ->where('m.name','=',$module)
     ->where('r.role','=',$role)
     ->where('q.status','=',$status)
     ->where('q.approver_empid','=',$approver_id)
     ->distinct()->select(DB::raw('COUNT(DISTINCT q.application_id) AS app_count'))->count();
    }

    public static function get_application_ids_by_queue_status($cid,$module,$role,$approver_id,$status)
    {

        $res= DB::select(DB::raw("SELECT GROUP_CONCAT(q.application_id) AS ids
            FROM approval_queue q
            LEFT JOIN roles r ON q.role_id=r.id
            LEFT JOIN modules m ON q.module_id=m.id
            WHERE q.cid='$cid' AND m.name='$module' AND r.role='$role' AND q.approver_empid='$approver_id' AND q.`status`IN (".($status?$status:"''").")
                            "));
        return $res[0]->ids;
    }

    public static function getNextApprovalIdForApplication($module_id,$role_id,$application_id,$approver_id,$cid){

        $queue_id = DB::table('approval_queue')
            ->where('module_id',$module_id)
            ->where('role_id',$role_id)
            ->where('application_id',$application_id)
            ->where('approver_empid',$approver_id)
            ->where('cid',$cid)
            ->pluck('id')
            ->first();

        if($queue_id){
            return DB::table('approval_queue')
                ->where('id', '>', $queue_id)
                ->where('application_id',$application_id)
                ->where('module_id',$module_id)
                ->where('cid',$cid)
                ->orderBy('id')
                ->select('approver_empid','role_id')
                ->first();

        } else {
            return false;
        }


    }

    public static function getFirstApproverForApplication($module_id,$application_id,$cid){

        return DB::table('approval_queue')
            ->where('module_id',$module_id)
            ->where('application_id',$application_id)
            ->where('cid',$cid)
            ->orderBy('id')
            ->first();
    }
}
