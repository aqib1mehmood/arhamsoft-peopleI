<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\city;
use App\Models\state;
use App\Models\bank_branch;
use App\Models\proll_reference_data;
use App\Http\Resources\common\CountryResource;
use App\Models\Role;
use App\Models\Department;
use App\Models\department_group_level;
use App\Models\department_group_heirarchy;
use App\Models\DepartmentManager;
use App\Models\Band;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use App\Models\UniquePosition;
use App\Models\Hr_Services\Reason;
use App\Models\Hr_Services\Loan_Advance\la_types;
class CommonController extends Controller
{
    public function countries(Request $request)
    {
        
        $countries_list = CountryResource::collection(country::get());
        if($countries_list->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No country Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$countries_list
            ],
            200); 
        }  
    }
    public function departments(Request $request){
        $departments = Department::where('type','=','department')->where('status','=',1)->get();
        if($departments->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Departments Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$departments
            ],
            200); 
        }  
    }
    public function job_family_or_group(Request $request){
        
        $level_name = $request->input('name');
        $group_id = $request->input('group_id');
        
        $department_levels = department_group_heirarchy::where('group_id','=',$group_id)->wherehas('department_group_level',function($q) use($level_name){
            $q->where('name','=',$level_name);
        })->get();
        if($department_levels->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Department levels Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$department_levels
            ],
            200); 
        }  
    }
    public function portals(Request $request){
        
        $portals =  DB::select(DB::raw("SELECT portal_id, portal FROM sys_portal WHERE app_view_config=1;"));
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$portals
            ],
            200); 
    }
    public function companies(Request $request){
        $companies = DB::select(DB::raw("SELECT id, companyname
        FROM proll_client WHERE STATUS=1 group by `companyname`;"));
         return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$companies
            ],
            200); 
    }
    public function company_branches($client_id,Request $request){
          
        $company_branches =  DB::select(DB::raw("SELECT loc_id, loc_desc
        FROM proll_client_location WHERE cid='$client_id' group by loc_desc;"));
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$company_branches
            ],
            200);
    }
    public function approval_sequence(Request $request){
        $sql="SELECT id, approval_text
    	FROM approval_sequence
	    group by approval_text;";
        $approval_sequences = DB::select(DB::raw($sql));
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$approval_sequences
            ],
            200); 
    }
    public function hr_persons(Request $request){
        $sql="SELECT id, auth_repre_name
    	FROM proll_client_assist
	    WHERE `status`=1 and emp_id<>'' and emp_id<>0;";
        $hr_persons = DB::select(DB::raw($sql));
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$hr_persons
            ],
            200); 
    }
    public function roles(Request $request){
        
        $roles =  DB::select(DB::raw("SELECT DISTINCT  ac.role_id,r.role
        FROM approval_config ac
        LEFT JOIN  roles r ON ac.role_id=r.id
        INNER JOIN approval_role_config rc ON ac.id=rc.approval_config_id;"));
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$roles
            ],
            200); 
    }
    public function Reference_drop_down(Request $request,$filter_name){
        
        $references = proll_reference_data::wherehas('proll_reference_data_code',function($q) use($filter_name){
            
            $q->where('reference_code','=',$filter_name);
        })->select('ref_id','description','id','reference_key')->get();


        if($references->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No References Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$references
            ],
            200); 
        }  
    }
    public function get_loan_types($emp_id,Request $request){
        
        $emp_info=DB::Table('proll_employee as e')
        ->leftjoin('proll_employee_detail as ed','e.id','ed.empid')
        ->where('e.id','=',$emp_id)
        ->select('e.emp_band','e.designation','ed.employeementRegionCurrent as country_id','e.department_id',DB::raw("TIMESTAMPDIFF( MONTH, e.doj, NOW() ) as months_in_org"))
        ->first();
        $emp_info = (array) $emp_info;
        $loan_types = DB::Table('la_types as lt')
        ->leftjoin('la_setup as ls','ls.loan_type','=','lt.id')
        ->leftjoin('la_setup_dept as sd','ls.id','=','sd.setup_id')
        ->leftjoin('la_setup_desig as desig','ls.id','=','desig.setup_id')
        ->leftjoin('la_setup_bands as sb','ls.id','=','sb.setup_id')
        ->where('ls.loan_type_applicable','=',1)
        ->where('ls.country_id','=',$emp_info["country_id"])
        ->where('sd.dept_id','=',$emp_info['department_id'])
        ->orwhere('sd.dept_id','=',-1)
        ->where('sb.band_id','=',$emp_info['emp_band'])
        ->orwhere('sb.band_id','=',-1)
        ->where('desig.desig_id','=',$emp_info['designation'])
        ->orwhere('desig.desig_id','=',-1)
        ->where('ls.loan_activation','<=',$emp_info['months_in_org'])  
        ->select('ls.id','lt.loan_type')
        ->get();      
        return response()->json(
                    [
                        'status'=>'pass',
                        'message'=>'success',
                        'data'=>$loan_types
                    ],
                    200); 
   
    }
    public function get_employees(Request $request){
        $sql = "SELECT DISTINCT e.id, e.name
        FROM la_requests lr
        LEFT JOIN proll_employee e ON lr.empid=e.id
        WHERE lr.application_status=2 ORDER BY e.name;";
        $employees = DB::Select(DB::Raw($sql));

        if(!$employees){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No employees Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$employees
            ],
            200); 
        }  
    }
    public function Reference_drop_down_detail(Request $request,$filter_name,$id){
        
        $reference = proll_reference_data::wherehas('proll_reference_data_code',function($q) use($filter_name){
            
            $q->where('reference_code','=',$filter_name);
        })->select('ref_id','description','id','reference_key')->where('id','=',$id)->first();


        if(!$reference){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No References Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$reference
            ],
            200); 
        }  
    }
    public function  emp_code_max_length(Request $request){
        $emp_code_max_length = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            
            $q->where('reference_code','=','Employee_Code_Max_Lenght_Dropdown');
        })->select('ref_id','description','id','reference_key')->get();


        if($emp_code_max_length->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No References Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$emp_code_max_length
            ],
            200); 
        }  

    }
    public function lm_types($type,Request $request){
        if($type == "email"){
            $lm_list = DepartmentManager::select('id','email',"empcode","line_manager")->get();
        }else if($type == "code"){
            $lm_list = DepartmentManager::select('id','empcode',"empcode","line_manager")->get();
        }else if($type == "parent_lm"){
            $lm_list = DepartmentManager::select('id','line_manager',"empcode","line_manager")->where('parent_reporting_lm','!=',0)->get();
        }
        if($lm_list->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Lm Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$lm_list
            ],
            200); 
        }  
    }
    public function lm_levels(Request $request){
        $lm_levels = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            
            $q->where('reference_code','=','main_lm');
        })->select('ref_id','description','id')->get();


        if($lm_levels->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No References Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$lm_levels
            ],
            200); 
        }  
    }
    public function emp_status(Request $request){
        $emp_status_dropdown = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Job_Status');
        })->select('description',"reference_key","ref_id","id")->get();

        if($emp_status_dropdown->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No References Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$emp_status_dropdown
            ],
            200); 
        }  
    }

    public function job_family_or_group_detail($id,Request $request){
        
        $department_levels = department_group_heirarchy::where('parent_id','=',$id)->where('status','=',1)->get();
        if($department_levels->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Department group heirarchy Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$department_levels
            ],
            200); 
        }  
    }
    public function unique_positions_on_departments($id,Request $request){
        
        $unique_positions = UniquePosition::where('department_id','=',$id)->get();
        if($unique_positions->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Unique Positions Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$unique_positions
            ],
            200); 
        }  
    }

    public function sub_departments($id,Request $request){
        $sub_departments = Department::where('reporting_department_id','=',$id)->where('type','=','sub_department')->where('status','=',1)->get();
        if($sub_departments->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Sub Departments Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$sub_departments
            ],
            200); 
        }  
    }
    public function Reasons($client_id,Request $request){
        $reasons = Reason::where('display','=',1)->where('cid','=',$client_id)->get();
        if($reasons->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No Reasons Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$reasons
            ],
            200); 
        }  
    }
    public function states(Request $request)
    {
        $country_id = $request->{'country_id'};
        $states_list = CountryResource::collection(state::where('country_id','=',$country_id)->get());
        if($states_list->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No state Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$states_list
            ],
            200); 
        }  
    }
    public function manage_hr_dropdowns(Request $request){
        
        $roles = Role::select('id','role')->get();
            $name_salute_dropdown = proll_reference_data::wherehas('proll_reference_data_code',function($q){
                $q->where('reference_code','=','name_salutation');
            })->select('description',"reference_key","ref_id","id")->get();
            
            $emp_status_dropdown = proll_reference_data::wherehas('proll_reference_data_code',function($q){
                $q->where('reference_code','=','Job_Status');
            })->select('description',"reference_key","ref_id","id")->get();

            return response()->json(
                [
                    'status'=>'pass',
                    'message'=>'success',
                    'data'=>array('roles'=>$roles,"name_salute"=>$name_salute_dropdown,"emp_status"=>$emp_status_dropdown)
                ],
                200); 

    }
    public function branch_types(Request $request)
    {
        $branch_types = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            
            $q->where('reference_code','=','Branch Type');
        })->select('ref_id','description','id')->get();
        
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$branch_types
            ],
            200); 
          
    }
    public function bankbranches(Request $request){
        $bank_id = $request->{'bank_id'};
        $bank_branches = bank_branch::where('bank_id','=',$bank_id)->get();
        if($bank_branches->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No bank branches Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$bank_branches
            ],
            200); 
        }  
    }
    public function cities(Request $request)
    {
        $state_id = $request->{'state_id'};
        $city_list = CountryResource::collection(city::where('state_id','=',$state_id)->get());
        if($city_list->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No city Available'
                ],
                200); 
        }
        else
        {
        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>$city_list
            ],
            200); 
        }  
    }
    //ap for travel
    
    /***************************** Travel Module Common Controller ***********************/

    public function travel_types()
    {
        $travel_scope = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Travel_Scope');
        })->select('description',"reference_key","ref_id","id","flag")->get();

        if($travel_scope->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $travel_scope,
            ],
            200);
        }
    }

    public function expense_types()
    {
        $expense_types = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Expense_Type');
        })->select('description',"reference_key","ref_id","id","flag")->get();

        if($expense_types->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $expense_types,
            ],
            200);
        }
    }

    public function transport_and_accomodation_arrangement(){
        $travel_arrangment = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Transport_Arrangement');
        })->select('description',"reference_key","ref_id","id",'flag')->get();


        if($travel_arrangment->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $travel_arrangment,
            ],
            200);
        }
    }

    public function expense_frequency()
    {
        $expense = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Expense_Frequency');
        })->select('description',"reference_key","ref_id","id","flag")->get();

        if($expense->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $expense,
            ],
            200);
        }
    }
    
    /******************************** Not Required Yet *************************************/

    public function city_branches(Request $request)
    {
        $city_id = $request->{'city_id'};
        $branches = DB::table('proll_client_location')->where('city_id',$city_id)->get();
        if($branches->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $branches,
            ],
            200);
        }
    }
    public function get_markets(Request $request){
        $company_id = $request->{'company_id'};
        $markets = DB::table('geo_company_groups')->where('company_id',$company_id)->where('geo_group_id',1)->get();
        if($markets->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $markets,
            ],
            200);
        }
    }

    public function clusters(Request $request)
    {
        $company_id = $request->{'company_id'};
        $market_id = $request->{'market_id'};
        $clusters = DB::table('geo_company_groups')->where('company_id',$company_id)->where('parent_id',$market_id)->get();
        if($clusters->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $clusters,
            ],
            200);
        }
    }

    public function sub_clusters(Request $request)
    {
        $company_id = $request->{'company_id'};
        $cluster_id = $request->{'cluster_id'};
        $sub_clusters = DB::table('geo_company_groups')->where('company_id',$company_id)->where('parent_id',$cluster_id)->get();
        if($sub_clusters->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $sub_clusters,
            ],
            200);
        }
    }

    public function employment_type()
    {
        $employment_type = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Employment_Type');
        })->select('description',"reference_key","ref_id","id")->get();

        if($employment_type->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $employment_type,
            ],
            200);
        }
    }

    public function band()
    {
        $band = Band::where('band_status',1)->select('id','unified_band')->get();

        if($band->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $band,
            ],
            200);
        }
    }
    public function designation()
    {
        $designation = Designation::where('status',1)->select('designation_id','designation_name')->get();

        if($designation->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $designation,
            ],
            200);
        }
    }

    public function tenure()
    {
        $tenure = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Job_Tenure');
        })->select('description',"reference_key","ref_id","id")->get();

        if($tenure->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $tenure,
            ],
            200);
        }
    }

    public function religion()
    {
        $religion = DB::table('religion')->select('religion_id','religion_name')->get();

        if($religion->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $religion,
            ],
            200);
        }
    }

    public function gender()
    {
        $gender = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Employee_Gender');
        })->select('description',"reference_key","ref_id","id")->get();

        if($gender->isEmpty()){
            return response()->json([
                'status' => 'failed',
                'message' => 'No record found.',
            ],
            200);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'success',
                'data' => $gender,
            ],
            200);
        }
    }

    public function checkGeographicalFilters(Request $request)
    {
        $cid = $request->{'client_id'};

        $check = proll_reference_data::wherehas('proll_reference_data_code',function($q){
            $q->where('reference_code','=','Geographical_Filter');
        })
        ->where('cid',$cid)
        ->select('description','flag')->get();

        if(count($check) < 1){
            return response()->json([
                'status' => 'pass',
                'message' => 'Success',
                'data' => "No Data Found",
                'code' => 200,
            ]);
        }else{
            return response()->json([
                'status' => 'pass',
                'message' => 'Success',
                'data' => $check,
                'code' => 200,
            ]);
        }

        
    }
}
