<?php

namespace App\Http\Controllers;

use App\Models\Employee;

use App\Expense;
use App\Helpers\MultiApprovalHelpers;
use App\Leave;
use App\Loan;
use App\Module;
use App\Travel;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Http\Requests\DownloadCredientialsRequest;
use App\Http\Requests\AddOrUpdateManageHrRequest;
use App\Http\Requests\EmployeeDetailsRequest;
use App\Http\Requests\ProllClientAssistDeleteRequest;
use App\Http\Traits\CsvValidateTrait;
use App\Models\proll_client_assist;
use App\Models\proll_reference_data;
use App\Models\Role;
use App\Http\Resources\ProllClientAssistResource;

class EmployeeController extends Controller
{
    use CsvValidateTrait;

        public function login(Request $request){
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'app_token' => 'required',
            'os_type' => 'required',
            'app_version' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                    'code'=>'401'
                ]);
        }
        $email =$request->email;
        $password =$request->password;
        $token='';
        $user_info=null;
        $Roles=array();$available_modules=array();
        $check_email = DB::table('proll_employee')->where('loginname', $email)->exists();
    
        if($check_email){
            $users = DB::table('proll_employee')->where('loginname', $email)->first();
        
            if($users->e_pass_word==md5($password)){
                
                Employee::mobile_information($users->id,$request);
                $user_info=Employee::UserInformation_v2_3($users->id);
                $Roles=Employee::User_Roles_v2_3($users->id,$users->cid);
                $available_modules=Employee::available_modules();
                if(Auth::loginUsingId($users->id, true)){
                    $user=Auth::user();
                    $token=$user->createToken('MyApp')->accessToken;
                }
                $status = 'success';
                $code = '200';
                $error='';
                $user_id=$users->id;
                $client_id=$users->cid;
            }
            else
            {
                $status = 'failure';
                $code = '401';
                $error='Enter the correct password';
                $user_id='';
                $client_id='';


            }
        }else{
            $status = 'failure';
            $code = '401';
            $error='Enter the correct username or password';
            $user_id='';
            $client_id='';

        }

        return response()->json([
            'status' => $status,
            'code' => $code,
            'errors' => $error,
            'user_info' => $user_info,
            'user_id'=>$user_id,
            'client_id'=>$client_id,
            'token' => $token,
            'Roles' => $Roles,
            'available_modules' => $available_modules
        ]);


    }
    public  function logout(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'app_token' => 'required',
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
        $logout_status=Employee::EmployeeLogout($request);
        if($logout_status){
            $status='success';
            $code='200';
            $message='User has been logged out';
        }else{
            $status='failure';
            $code='401';
            $message='failure';
        }
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,

        ]);
    }

    /**
     * Show User Portal Notification .
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmPortalNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
            'client_id' => 'required',
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
        $user_id=decrypt($request->user_id);
        $user_role=$request->role_id;
        $cid=decrypt($request->client_id);
        $check= Employee::where('id','=',$user_id)->exists();
        $roles_list= Employee::check_user_Roles($user_id);
        $role_check=in_array($user_role,$roles_list);
        if($check && $role_check)
        {
            $updates_notification=Employee::EmployeeNotificationList($user_id,$user_role,$cid);
            $application_status=Employee::EmployeeNotificationstatus($user_id,$user_role,$cid);
            $status = 'success';
            $code = '200';
            $roles= Employee::User_Roles($user_id,$cid);
        }
        else
        {
            $status = 'failure';
            $code = '401';
            $roles=null;
            $updates_notification=null;
            $application_status=null;
        }

        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'updates_notification' => $updates_notification,
            'application_status' => $application_status,
            'roles'=>$roles
        ]);


    }

    public function getEmPortalNotification_v2_3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
            'client_id' => 'required',
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
        $user_id=decrypt($request->user_id);
        $user_role=$request->role_id;
        $cid=decrypt($request->client_id);
        $check= Employee::where('id','=',$user_id)->exists();
        $roles_list= Employee::check_user_Roles($user_id);
        $role_check=in_array($user_role,$roles_list);
        if($check && $role_check)
        {
            $updates_notification=Employee::EmployeeNotificationList_v2_3($user_id,$user_role,$cid);
            $application_status=Employee::EmployeeNotificationstatus_v2_3($user_id,$user_role,$cid);
            $status = 'success';
            $code = '200';
            $roles= Employee::User_Roles_v2_3($user_id,$cid);
        }
        else
        {
            $status = 'failure';
            $code = '401';
            $roles=null;
            $updates_notification=null;
            $application_status=null;
        }

        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'updates_notification' => $updates_notification,
            'application_status' => $application_status,
            'roles'=>$roles
        ]);


    }
    /**
     * Show the form for Applications Status \.
     *
     * @return leave travel expense details on the base of User ID
     */

    public function getApplicationsStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
            'client_id' => 'required',
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
        $user_id=decrypt($request->user_id);
        $role_id=$request->role_id;
        $cid=decrypt($request->client_id);

        $check= Employee::where('id','=',$user_id)->exists();
        $roles_list= Employee::check_user_Roles($user_id);
        $role_check=in_array($role_id,$roles_list);


        /*------------------------------LEAVE--------------------------------*/
    if($check && $role_check ) {
        $result=Employee::EmployeeNotificationstatus($user_id,$role_id,$cid);
        $status ='success';
        $code=200;
     }else
     {
            $status = 'failure';
            $code = '201';
            $result='';
     }
        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'result' => $result,
        ]);

    }

    public function GetDetailsForCalenderDate(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'date' => 'required'
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

       $user_id =decrypt($request->user_id);
       $date =$request->date;
        $attendance=0;
        try{

        $check= Employee::where('id','=',$user_id)->exists();
        if($check){
            $attendance= User::UserAttendanceDetails($user_id,$date);

            $status ='success';
            $code=200;
        }else{
            $status = 'failure';
            $code = '201';
        }
        }
        catch (\Exception $e){
            return response()->json([
                'status'=>'Failure',
                'code'=>'400',
                'Attendance' =>'',

            ]);
        }

        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'Attendance' =>$attendance,

        ]);



    }

    public function ReportingEmployeeAttendanceList(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'date' => 'required',
            'role_id' => 'required',
            'cid' => 'required',

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


 try {

     $user_id =decrypt($request->user_id);

     $date =$request->date;

     $role_id=$request->role_id;

     $cid=decrypt($request->cid);



     $attendance=0;
     $check= Employee::where('id','=',$user_id)->exists();

     if ($check) {

         $attendance = User::UserAttendanceListDetails($user_id, $date, $role_id, $cid);
         if ($attendance != 0) {
             $status = 'success';
             $code = 200;
         } else {
             $status = 'failure';
             $code = '201';
         }
     } else {
         $status = 'failure';
         $code = '201';
     }
 }
 catch (\Exception $e){
     return response()->json([
         'status'=>'Failure',
         'code'=>'400',
         'Attendance' =>array(),

     ]);
 }
        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'Attendance' =>$attendance,

        ]);

    }

//  LM get notifications Updates

    public function LMGetNotificationUpdates(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'required',
            'dept_id' => 'required'
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


        $user_id=$request->user_id;
        $role_id=$request->role_id;
        $dept_id=$request->dept_id;
        $leave_notifications=0;
        $expense_notifications=0;
        $travel_notifications=0;

        $user= Employee::where('id','=',$user_id)->exists();
        $dept= Employee::where('dept_id','=',$dept_id)->exists();
        $roles_list= Employee::check_user_Roles($user_id);
        $role_check=in_array($role_id,$roles_list);

        if($user && $dept && $role_check){
            $leave_notifications=Leave::LMLeaveNotifications($dept_id);
            $expense_notifications=Expense::LMExpenseNotifications($dept_id);
            $travel_notifications=Travel::LMTravelNotifications($dept_id);
            $status ='success';
            $code=200;
        }else{
            $status = 'failure';
            $code = '201';
        }


        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'leave' =>$leave_notifications,
            'expense' =>$expense_notifications,
            'travel' =>$travel_notifications,

        ]);


    }

    public function GetMonthlyAttendanceReport(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'client_id' => 'required',
            'date' => 'required',
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


        $user_id=decrypt($request->user_id);
        $client_id=decrypt($request->client_id);
        $date=$request->date;
        $user= Employee::where('id','=',$user_id)->exists();

        if($user){
            $attendance= Employee::RosterMonthlyAttendance($user_id,$client_id,$date);
            $name= Employee::where('id','=',$user_id)->value('name');
            $status ='success';
            $code=200;
        }else{
            $status = 'failure';
            $code = '201';
            $name='';
            $attendance='';
        }


        return response()->json([
            'status'=>$status,
            'code'=>$code,
            'name'=>$name,
            'attendance'=>$attendance,

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        //
    }

    public function importUser(Request $request)
    {

        $users = (new FastExcel)->import($request->file, function ($lines) {
            $user = new User();
            $user->id = $lines['id'];
            $user->pass_word = $lines['pass_word'];
            $user->e_pass_word =md5($lines['pass_word']);
            $user->loginname =$lines['loginname'];
            $user->name =$lines['name'];
            $user->save();
            $user->createToken('people')->accessToken;
        });

        return response()->json(['success'=>'done'], $this->successStatus);
    }

    /*********EMPLOYEE PROFILE UPDATION API********/
    public function UpdatePersonalDetails($id, Request $request)
    {
        $id=base64_decode($id);
        //Basic Information
        $employee = User::find($id);
        $employee->name_salute =  $request['name_salute'];
        $employee->name =  $request['name'];
        $employee->f_hname =  $request['f_hname'];
        $employee->father_occupation =  $request['father_occupation'];
        $employee->dob =  $request['dob'];
        $employee->religion_id =  $request['religion_id'];
        $employee->native_language_id =  $request['native_language_id'];
        $employee->birth_country_id =  $request['birth_country_id'];
        $employee->birth_city_id =  $request['birth_city_id'];
        $employee->domicile_country_id =  $request['domicile_country_id'];
        $employee->domicile_city_id =  $request['domicile_city_id'];
        $employee->second_nationality_id =  $request['second_nationality_id'];
        $employee->blood_group_name =  $request['blood_group_name'];


        if(!empty($request->gender)){
            DB::table('proll_employee_detail')->where('empid', $id)->update(['gender' => $request['gender']]);
        }

        //End of Basic Information

        //Document Details
        $employee->cnic =  $request['cnic'];
        $employee->cnic_country_id =  $request['cnic_country_id'];
        $employee->cnic_issued_on =  $request['cnic_issued_on'];
        $employee->cnic_expiry =  $request['cnic_expiry'];
        $employee->blood_group_name =  $request['blood_group_name'];

        $employee->passport_no =  $request['passport_no'];
        $employee->passport_country_id =  $request['passport_country_id'];
        $employee->passport_issued_on =  $request['passport_issued_on'];
        $employee->passport_expiry =  $request['passport_expiry'];

        $employee->driving_license_number =  $request['driving_license_number'];
        $employee->driving_license_country_id =  $request['driving_license_country_id'];
        $employee->driving_license_issued_on =  $request['driving_license_issued_on'];
        $employee->driving_license_expiry =  $request['driving_license_expiry'];

        $employee->ntn_number =  $request['ntn_number'];
        $employee->ntn_country_id =  $request['ntn_country_id'];

        //End of Document Details

        $employee->m_status =  $request['m_status'];
        $employee->no_of_dependants =  $request['no_of_dependants'];
        $employee->ntn_country_id =  $request['ntn_country_id'];

        //Dependents Detail
        $employee->save();
        $nominies=$request->nominies;
        Employee::updateNominiesDetail($id, $nominies);
        //End of Dependants Detail

        return response()->json([
            'status' => 'success',
            'message' => 'Personal Details Updated Successfully'
        ],200);
    }
    /*********END OF EMPLOYEE PROFILE UPDATION API********/



    /*********EMPLOYEE CONTACT DETAILS UPDATION API*********/
    public function UpdateContactDetails($id, Request $request)
    {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'data' => 'Invalid employee ID'
            ],400);
        }

        $contact_details=$request->contact_detail;
        return Employee::UpdateContactDetails($id,$contact_details);
        //End of Permanent Contact Details
    }
    /***********END OF EMPLOYEE CONTACT DETAILS API***********/



    /***********EMPLOYEE EDUCATION & SKILLS UPDATION API***********/
    public function UpdateEducationSkillsAndLanguages($id, Request $request)
    {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'data' => 'Invalid employee ID'
            ],400);
        }

        $education = $request->education_data;
        $skills = $request->skills_data;
        $languages = $request->language_data;
        Employee::updateEducation($id,$education);
        Employee::updateSkills($id,$skills);
        Employee::updateLanguages($id,$languages);

        return response()->json([
            'status' => 'success',
            'message' => 'Educational Details Updated Successfully'
        ],200);
    }
    /***********END OF EMPLOYEE EDUCATION & SKILLS UPDATION APIs***********/

    /***********EMPLOYEE WORK EXPERIENCE UPDATION API************/
    public function UpdateWorkExperience($id, Request $request)
    {

        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'data' => 'Invalid employee ID'
            ],400);
        }
        $experience = $request->work_experience;

        foreach($experience as $data){
            if($data['job_to'] < $data['job_from'])
            {
                return response()->json([
                    'status' => 'failure',
                    'code'   => 401,
                    'message' => 'Job Date To, cannot be less than Job Date From (experience_id: '.$data['experienc_id'].')'
                    ]);
            }
            else
            {
               Employee::updateWorkExperience($id,$experience);
            }

        }
        return response()->json([
            'status' => 'success',
            'message' => 'Work Experience Updated Successfully'
        ],200);
    }
    /***********END OF EMPLOYEE WORK EXPERIENCE UPDATION API***********/



    /***********EMPLOYEE REFRENCES UPDATION API***********/
    public function UpdateReferences($id, Request $request)
    {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
        $references = $request->references;
        Employee::updateReferences($id,$references);

        return response()->json([
            'status' => 'success',
            'message' => 'Refrences Updated Successfully'
        ],200);
    }
    /***********END OF EMPLOYEE REFRENCES UPDATION API***********/



    /***********EMPLOYEE BANK DETAILS UPDATION API***********/
    public function UpdateBankDetails($id, Request $request)
    {

        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
        Employee::updateBankDetails($id,$request);
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Bank Details Updated Successfully'
        ]);
    }
    /***********END OF EMPLOYEE BANK DETAILS UPDATION API***********/


    /*************Employee Personal Details Retrival API*********************/
    public function GetPersonalDetails($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
        else {
            $personalDetails=Employee::PersonalDetails($id);
            $nominies=Employee::getEmployeeNomineis($id);
                return response()->json([
                    'status' => 'success',
                    'data' => ['basic_information'=>$personalDetails,'nominies'=>$nominies]
                ],200);
        }
    }
    /*************End of Employee Personal Details Retrival API*********************/


    /*************Employee Employment Details Retrival API*********************/
    public function GetEmploymentDetails($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if($employee) {
            $employment_details = Employee::EmploymentDetails($id);
            return response()->json([
                'status' => 'success',
                'data' => $employment_details
            ],200);
        }
        else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
    }
    /*************End of Employee Employment Details Retrival API*********************/


    /*************Employee Work Experience Retrival API*********************/
    public function GetWorkExperience($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee){
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }else{
            $work_experience= Employee::GetWorkExperience($id);
            if($work_experience){
                return response()->json([
                    'status' => 'success',
                    'data' => $work_experience
                ],200);
            }else{
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Record not found!'
                ],404);
            }
        }
    }
    /*************End of Employee Work Experience Retrival API*********************/


    /*************Employee Education, Skills & Languages Retrival API*********************/
    public function EducationSkillsAndLanguages($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if($employee) {
            $education = Employee::Education($id);
            $skills    = Employee::Skills($id);
            $languages = Employee::Languages($id);
            return response()->json([
                'status' => 'success',
                'data' => ['education_data'=>$education, 'skills_data'=>$skills, 'language_data'=>$languages]
            ],200);
        }
        else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
    }
    /*************End of Employee Education, Skills & Languages Retrival API*********************/


    /*************Employee Contact Details Retrival API*********************/
    public function GetContactDetails($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if($employee) {
            $data = Employee::ContactDetails($id);
            return response()->json([
                'status' => 'success',
                'data' => $data
            ],200);
        }
        else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
    }
    /*************End of Employee Contact Details Retrival API*********************/


    /*************Employee References Retrival API*********************/
    public function GetReferences($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if($employee) {
            $references = Employee::References($id);
            return response()->json([
                'status' => 'success',
                'data' => $references
            ],200);
        }
        else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
    }
    /*************End of Employee Refrences Retrival API*********************/


    /*************Employee Bank Details Retrival API*********************/
    public function GetBankDetails($id) {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if($employee) {
            $bank_details = Employee::BankDetails($id);
            return response()->json([
                'status' => 'success',
                'data' => $bank_details
            ],200);
        }
        else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Invalid employee ID'
            ],400);
        }
    }
    /*************End of Employee Bank Details Retrival API*********************/


    /*************Roster Employee APIs For LM*********************/
    //Roster Employees By Department
    public function GetRosterEmployees(Request $request) {
    $dept_id = $request->dept_id;
    $roster_employees = Employee::RosterEmployees($dept_id);

    if($roster_employees->count() > 0) {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $roster_employees
        ]);
    }
    else {
        return response()->json([
            'status' => 'failure',
            'code' => 401,
            'message' => 'Record not found'
        ]);
    }
}

    //Shift Roster Detail
    public function GetShiftRosterDetail(Request $request) {
        $validator = Validator::make($request->all(), [
                    'dept_id' => 'required',
                    'shift_id' => 'required',
                    'date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                            [
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }

        $dept_id = $request->dept_id;
        $shift_id = $request->shift_id;
        $shift_roster_detail = Employee::ShiftRosterDetails($dept_id, $request->date, $shift_id);

        if ($shift_roster_detail->count() == 0) {
            return response()->json([
                        'status' => 'failure',
                        'code' => 404,
                        'message' => 'Record not found'
            ]);
        } else {
            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'data' => $shift_roster_detail
            ]);
        }
    }


//Roster Schedular For Employee
    public function GetRosterSchedularForEmployee(Request $request) {
        $validator = Validator::make($request->all(), [
                    'employee_id' => 'required',
                    'date_from' => 'required',
                    'date_to' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                            [
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }
//    $emp_id     = DB::table('proll_employee')->where('name', $request->name)->value('id');
        $emp_id = $request->employee_id;
        $dept_id = DB::table('proll_employee')
                ->leftJoin('proll_department_managers', 'proll_department_managers.id', '=', 'proll_employee.dept_id')
                ->leftJoin('department_hierarchy', 'department_hierarchy.id', '=', 'proll_department_managers.department_hierarchy_id')
                ->where('proll_employee.id', $emp_id)
                ->value('department_hierarchy.id');

        $date_from = $request->date_from;
        $date_to = $request->date_to;
        $roster = Employee::getEmployeeRoster($emp_id, $date_from, $date_to);
        $dept_shifts = Employee::getDepartmentShifts($dept_id);

        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'data' => [ 'roster' => $roster, 'shifts' => $dept_shifts]
        ]);
    }

    //Update Roster Time In, Time Out
    // shift id should be with this script we will confirm which shift time update
    public function UpdateRosterTimeInTimeOut(Request $request) {
        $validator = Validator::make($request->all(), [
                    'employee_id' => 'required',
                    'roster_date' => 'required',
                    'time_in' => 'required',
                    'time_out' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                            [
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }

        $updated_time_in = $request->time_in;
        $updated_time_out = $request->time_out;
//        dd($request->employee_id, $request->roster_date, $updated_time_in, $updated_time_out);

        $attendance = Employee::UpdateRosterTime($request->employee_id, $request->roster_date, $updated_time_in, $updated_time_out);

        if ($attendance) {
            $status = 'success';
            $code = '200';
            $message = 'Attendance time updated successfully';
        } else {
            $message = 'Attendance not updated due existing record for the same day and time.';
            $code = '401';
            $status = 'failure';
        }
        return response()->json([
                    'status' => $status,
                    'code' => $code,
                    'message' => $message
        ]);
    }

    //Add Employee CPL
    public function AddEmployeeCpl(Request $request) {
        $validator = Validator::make($request->all(), [
                    'employee_id' => 'required',
                    'cpl_to_add' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }
//        $current_cpl = DB::table('al_roster_overtime_cpl')->where('emp_id', $request->employee_id)->value('cpl');
//        $updated_cpl = $current_cpl + $request->cpl_to_add;
        Employee::AddCPL($request->employee_id, $request->cpl_to_add,$request->start_date,$request->end_date);
        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Employee CPL updated successfully'
        ]);
    }

    //Employee Shift Swap
    public function EmployeeShiftSwap(Request $request) {
        $validator = Validator::make($request->all(), [
                    'source_id' => 'required',
                    'source_date' => 'required',
                    'source_shift_id' => 'required',
                    'target_id' => 'required',
                    'target_date' => 'required',
                    'target_shift_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                            [
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }

        $source_id = $request->source_id;
        $source_shift_id = $request->source_shift_id;
        $source_date = $request->source_date;

        $temp_shift_id = $request->source_shift_id;
        $temp_source_date = $request->source_date;

        $target_id = $request->target_id;
        $target_shift_id = $request->target_shift_id;
        $target_date = $request->target_date;

        $time_in = DB::table('al_roster')->where(['emp_id' => $source_id, 'roster_date' => $source_date])->value('actual_shift_time_in');
        $time_out = DB::table('al_roster')->where(['emp_id' => $source_id, 'roster_date' => $source_date])->value('actual_shift_time_out');

        if (($time_in == null) && ($time_out == null)) {
            return response()->json([
                        'status' => 'failure',
                        'code' => 401,
                        'message' => "Employee shift is not assigned"
            ]);
        }
        if (($time_in != "0000-00-00 00:00:00") && ($time_out != "0000-00-00 00:00:00")) {
            return response()->json([
                        'status' => 'failure',
                        'code' => 401,
                        'message' => "Shift can't swap because employee already marked attendance on requested day."
            ]);
        } else {

            DB::table('al_roster')->where(['emp_id' => $source_id, 'roster_date' => $source_date])
                    ->update([
                        'shift_id' => $target_shift_id,
                        'roster_date' => $target_date
            ]);

            DB::table('al_roster')->where(['emp_id' => $target_id, 'roster_date' => $target_date])
                    ->update([
                        'shift_id' => $temp_shift_id,
                        'roster_date' => $temp_source_date
            ]);

            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Shifts swaped successfully'
            ]);
        }
    }

    //Roster Requests
    public function GetRosterRequests(Request $request) {
        $validator = Validator::make($request->all(), [
            'lm_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error' => $validator->errors(),
                    'status' => 'failure',
                    'code' => '401'
                ], 401);
        }
        $roster_requests = Employee::RosterRequests($request->lm_id);
        if ($roster_requests->count() == 0) {
            return response()->json([
                        'status' => 'failure',
                        'code' => 400,
                        'message' => 'Requests not found'
            ]);
        }
        if ($roster_requests->count() > 0) {
            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'roster-requests' => $roster_requests
            ]);
        }
    }

    //LM Roster Calender
    public function GetRosterCalenderForLM(Request $request) {
        $dept_id = $request->dept_id;
        $selected_date = date('d M Y', strtotime($request->selected_date));

        $shifts = Employee::getDepartmentShifts($dept_id);
        if ($request->selected_date) {
            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'data' => [ 'selected_date' => $selected_date, 'shifts' => $shifts]
            ]);
        } else {
            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'data' => ['shifts' => $shifts]
            ]);
        }
    }

    //Shift Allocation List
    public function GetShiftAllocationList(Request $request) {
        $shift_id =$request->shift_id;
        $shift_name = DB::table('al_shift_setup')->where('shift_id', $shift_id)->value('name');

//        if ($shift_name == $request->shift) {
            $employees = Employee::ShiftAllocatedEmployees($request->dept_id, $shift_id, $request->selected_date);
            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'data' => ['employees' => $employees]
            ]);
//        }
    }

    public function RosterRequestConfirmationByLM(Request $request) {
        $request_type_id = DB::table('roster_request_types')->where('name', $request->request_name)->value('id');
        $request_exist = DB::table('al_roster_requests')
                        ->where(['emp_id' => $request->employee_id, 'client_id' => $request->client_id, 'request_type_id' => $request_type_id, 'request_date' => $request->request_date])->exists();
        if ($request_exist) {
            DB::table('al_roster_requests')
                    ->where(['emp_id' => $request->employee_id, 'client_id' => $request->client_id, 'request_type_id' => $request_type_id, 'request_date' => $request->request_date])
                    ->update(['status' => $request->status, 'lm_status_reason' => $request->lm_status_reason]);

            return response()->json([
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Request is ' . $request->status
            ]);
        } else {
            return response()->json([
                        'status' => 'failure',
                        'code' => 400,
                        'message' => 'Request not found'
            ]);
        }
    }

    /*     * ***********End of Roster Employee APIs For LM******************** */

    /*     * ***********Employee Roster APIs For Employee******************** */

    public function GetEmployeeRosterSchedular(Request $request) {
        $date_from = $request->date_from;
        $date_to = $request->date_to;
//  $dept_id = DB::table('proll_employee')->where('id', $request->emp_id)->value('dept_id');
        $dept_id = DB::table('proll_employee')
                ->leftJoin('proll_department_managers', 'proll_department_managers.id', '=', 'proll_employee.dept_id')
                ->leftJoin('department_hierarchy', 'department_hierarchy.id', '=', 'proll_department_managers.department_hierarchy_id')
                ->where('proll_employee.id', $request->emp_id)
                ->value('department_hierarchy.id');
        $roster = Employee::EmployeeRosterForEmployee($request->emp_id, $date_from, $date_to);
        $shifts = Employee::getDepartmentShifts($dept_id);
        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'data' => [ 'roster' => $roster, 'shifts' => $shifts]
        ]);
    }

    public function EmployeeRosterRequest(Request $request) {
        $request_type = DB::table('roster_request_types')->where('name', $request->request_type)->value('id');
        $current_date = date('Y-m-d');
        $dept_id = DB::table('proll_employee')->where('id', $request->emp_id)->value('dept_id');
        $line_manager = DB::table('proll_department_managers')->where('id', $dept_id)->value('id');
        // CPL REQUEST
        if ($request_type == 1) {
            $query_result = DB::table('al_roster_requests')->where(['emp_id' => $request->emp_id, 'request_type_id' => $request_type, 'request_date' => $request->selected_date, 'client_id' => $request->client_id])->exists();
            if ($query_result == false) {
                if ($request->selected_date < $current_date) {
                    return response()->json([
                                'status' => 'failure',
                                'code' => 400,
                                'message' => "Sorry! you cannot apply CPL for previous date from current date."
                    ]);
                } else {
                    $data = array(
                        'request_type_id' => $request_type,
                        'emp_id' => $request->emp_id,
                        'client_id' => $request->client_id,
                        'request_date' => $request->selected_date,
                        'reason' => $request->reason,
                        'line_manager' => $line_manager,
                        'created_date' => $current_date,
                    );
                    DB::table('al_roster_requests')->insert($data);
                    return response()->json([
                                'status' => 'success',
                                'code' => 200,
                                'message' => "Yor CPL request is submitted"
                    ]);
                }
            } else {
                return response()->json([
                            'status' => 'failure',
                            'code' => 400,
                            'message' => "CPL already applied for requested date."
                ]);
            }
        }
        // ADDITIONAL SHIFT REQUEST
        if ($request_type == 2) {
            $query_result = DB::table('al_roster_requests')->where(['emp_id' => $request->emp_id, 'request_type_id' => $request_type, 'request_date' => $request->selected_date, 'client_id' => $request->client_id])->exists();
            if ($query_result == false) {
                if ($request->selected_date < $current_date) {
                    return response()->json([
                                'status' => 'failure',
                                'code' => 400,
                                'message' => "Sorry! you cannot apply additional shift for previous date from current date."
                    ]);
                } else {
                    $new_shift = DB::table('al_shift_setup')->where(['shift_id' => $request->shift_id, 'cid' => $request->client_id])->value('shift_id');
                    $data = array(
                        'request_type_id' => $request_type,
                        'emp_id' => $request->emp_id,
                        'client_id' => $request->client_id,
                        'request_date' => $request->selected_date,
                        'reason' => $request->reason,
                        'line_manager' => $line_manager,
                        'created_date' => $current_date,
                        'additional_shift' => $new_shift
                    );
                    DB::table('al_roster_requests')->insert($data);
                    return response()->json([
                                'status' => 'success',
                                'code' => 200,
                                'message' => "Yor Additional Shift request is submitted."
                    ]);
                }
            } else {
                return response()->json([
                            'status' => 'failure',
                            'code' => 400,
                            'message' => "Shift already added on requested date."
                ]);
            }
        }
        // CHANGE SHIFT REQUEST
        if ($request_type == 3) {
            $query_result = DB::table('al_roster')->where(['emp_id' => $request->emp_id, 'roster_date' => $request->selected_date, 'shift_id' => $request->shift_id, 'cid' => $request->client_id])->exists();
            if ($query_result == true) {
                if ($request->selected_date < $current_date) {
                    return response()->json([
                                'status' => 'failure',
                                'code' => 400,
                                'message' => "Sorry! you cannot change shift for previous date from current date."
                    ]);
                } else {
                    $shift_to_change = DB::table('al_shift_setup')->where(['shift_id' => $request->shift_id, 'cid' => $request->client_id])->value('shift_id');
                    if ($request->change_with_date < $current_date) {
                        return response()->json([
                                    'status' => 'failure',
                                    'code' => 400,
                                    'message' => "Sorry! shift cannot be changed with previous date shift."
                        ]);
                    } else {
                        $result = DB::table('al_roster_requests')->where(['emp_id' => $request->emp_id, 'client_id' => $request->client_id, 'change_with_date' => $request->change_with_date])->exists();
                        if ($result == true) {
                            return response()->json([
                                        'status' => 'failure',
                                        'code' => 400,
                                        'message' => "Sorry! your request is already submitted for this date."
                            ]);
                        } else {
                            $query_result = DB::table('al_roster')->where(['roster_date' => $request->change_with_date, 'emp_id' => $request->emp_id, 'cid' => $request->client_id])->exists();
                            if ($query_result == true) {
                                $shift_to_change_with = DB::table('al_roster')->where(['roster_date' => $request->change_with_date, 'emp_id' => $request->emp_id, 'dept_id' => $dept_id, 'cid' => $request->client_id])->value('shift_id');
                                $data = array(
                                    'request_type_id' => $request_type,
                                    'emp_id' => $request->emp_id,
                                    'client_id' => $request->client_id,
                                    'request_date' => $request->selected_date,
                                    'change_with_date' => $request->change_with_date,
                                    'reason' => $request->reason,
                                    'line_manager' => $line_manager,
                                    'created_date' => $current_date,
                                    'shift_to_change' => $shift_to_change,
                                    'shift_to_change_with' => $shift_to_change_with
                                );
                                DB::table('al_roster_requests')->insert($data);
                                return response()->json([
                                            'status' => 'success',
                                            'code' => 200,
                                            'message' => "Your Shift Change request is submitted."
                                ]);
                            } else {
                                return response()->json([
                                            'status' => 'failure',
                                            'code' => 400,
                                            'message' => "Sorry! new date shift slot is empty."
                                ]);
                            }
                        }
                    }
                }
            } else {
                return response()->json([
                            'status' => 'failure',
                            'code' => 400,
                            'message' => "Sorry! the selected date is missing in the roster."
                ]);
            }
        }
    }

    /*     * ***********End of Employee Roster APIs For Employee******************** */

    /*     * **************Generate AES code *************************** */

    public function EncryptAesCode(Request $request) {
        $value = encrypt($request->code);
        return response()->json([
                    'success' => 'done',
                    'result' => $value
        ]);
    }

    public function DecryptAesCode(Request $request) {
        $value = decrypt($request->code);
        return response()->json([
                    'status' => 'done',
                    'result' => $value
        ]);
    }

    public function LmViewEmployeeReportingList(Request $request) {
        $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'client_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                            [
                        'error' => $validator->errors(),
                        'status' => 'failure',
                        'code' => '401'
                            ], 401);
        }

        $user_id = decrypt($request->user_id);
        $client_id = decrypt($request->client_id);
        $employeeList = null;
        $user = Employee::where('id', '=', $user_id)->exists();
        $user_info = Employee::where('id', '=', $user_id)->first();
        if ($user && $user_info->cid == $client_id) {
            $roles_list = Employee::check_user_Roles($user_id);
            $role_check = in_array(2, $roles_list);
            if ($role_check) {
                $employeeList = Employee::ReportingUserInformation($user_id);
                $status = 'success';
                $code = 200;
            } else {
                $status = 'failure';
                $code = '201';
            }
        } else {
            $status = 'failure';
            $code = '201';
        }

        return response()->json([
                    'status' => $status,
                    'code' => $code,
                    'EmployeeList' => $employeeList,
        ]);
    }

    public function HR_Employee_List(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'client_id' => 'required',
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
        // dd($user_id);
        $client_id = decrypt($request->client_id);
        $check= Employee::where('id', $user_id)->exists();
        $roles_list= Employee::check_user_Roles($user_id);
        $role_check=in_array(3,$roles_list);
        if (!empty($user_id) && is_numeric($user_id) && $check && $role_check) {
            $List = Employee::HR_Employee_List($client_id);
            $success = 'success';
            $code = '200';


        } else {
            $success = 'failure';
            $code = '201';
            $List = '';
        }
        return response()->json([
            'status' => $success,
            'code' => $code,
            'Employee_List' => $List
        ]);
    }

    public function ViewProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
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
        $check= Employee::where('id', $user_id)->exists();
        if ($check) {
            $profile = Employee::EmployeeProfile($user_id);
            $success = 'success';
            $code = '200';


        } else {
            $success = 'failure';
            $code = '201';
            $profile = '';
        }
        return response()->json([
            'status' => $success,
            'code' => $code,
            'profile' => $profile
        ]);

    }

    public function GetDepartmentList(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'client_id' => 'required',
            'role_id' => 'required|numeric|min:3|max:5',
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
            try
            {
                $roles_list=Employee::check_user_Roles(decrypt($request->user_id));

                $user_role=$request->role_id;

                if(in_array($user_role,$roles_list))
                {
                    $department_List=Employee::DepartmentsList();

                    return response()->json([
                        'status' => 'success',
                        'code' =>'200',
                        'list' =>$department_List,
                    ]);
                }else
                {
                    return response()->json([
                        'status' => 'Failure',
                        'code' =>'401',

                    ]);
                }

            }
            catch (\Exception $e){

              return response()->json([
                  'status'=>'Failure',
                  'code'=>'400'
              ]);
            }


    }

    public function GetEmployeeListByDepartmentsId(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'client_id' => 'required',
            'role_id' => 'required|numeric|min:2|max:5',
            'dept_id' => 'required|numeric',
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
        try
        {
            $roles_list=Employee::check_user_Roles(decrypt($request->user_id));

            $user_role=$request->role_id;


            if(in_array($user_role,$roles_list))
            {
                $department_List=Employee::DepartmentsEmployeeList($request->dept_id,decrypt($request->user_id));

                return response()->json([
                    'status' => 'success',
                    'code' =>'200',
                    'list' =>$department_List,
                ]);
            }else
            {
                return response()->json([
                    'status' => 'Failure',
                    'code' =>'401',

                ]);
            }

        }
        catch (\Exception $e){

            return response()->json([
                'status'=>'Failure',
                'code'=>'400'
            ]);
        }

    }

    public function GetEmployeePasswordRecoverd(Request $request){
        $validator = Validator::make($request->all(), [
            'loginname' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                    'code'=>'401'
                ],
                401);
        }else
        {
            try
            {
                $check_email = DB::table('proll_employee')->where('loginname', $request->loginname)->exists();
                if($check_email){
                    $helper= new Helpers();
                  $helper->PasswordRecover($request->loginname);

                    $status = 'success';
                    $code ='200';
                    $message='Password has been recovered successfully please check your email';
                }else
                {
                    $status = 'Failure';
                    $code ='400';
                    $message='invalid Email Id';
                }

                return response()->json([
                    'status'=>$status,
                    'code'=>$code,
                    'message'=>$message

                ]);
            }
            catch (\Exception $e){
                return response()->json([
                    'status'=>'Failure',
                    'code'=>'400',
                    'message'=>'Failure'
                ]);
            }
        }
    }

    public function getEmployeesBasicInfo(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'client_id' => 'required',
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

    $user_id=decrypt($request->user_id);
    $client_id=decrypt($request->client_id);
    $user_exist= Employee::where('id', $user_id)->exists();
    $roles_list= Employee::check_user_Roles($user_id);
    $role_check=in_array(6,$roles_list); // 6=System
    if (!$user_exist || !$role_check) {
        return response()->json([
            'status' => 'failure',
            'code' => 401,
            'message' => 'Unauthorized request',
        ]);
    }
    $data=Employee::getEmployeesBasicInfo($client_id);
    return response()->json([
        'status'=>'success',
        'code'=>200,
        'data'=>$data

    ]);
}

    public function disclaimerAccepted(Request $request){
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'client_id' => 'required',
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
    $user_id=decrypt($request->user_id);
    $client_id=decrypt($request->client_id);
    $disclaimer_accepted=$request->disclaimer_accepted;
    $user_exist= Employee::where('id', $user_id)->exists();
    if (!$user_exist) {
        return response()->json([
            'status' => 'failure',
            'code' => 401,
            'message' => 'Unauthorized request',
        ]);
    }

    if($disclaimer_accepted==1){
        User::disclaimerAccepted($user_id,$client_id);
    }
    $res=User::isDisclaimerAccepted($user_id,$client_id);
    return response()->json([
        'status'=>'success',
        'code'=>200,
        'accepted'=>$res

    ]);
}


    /***********EMPLOYEE EMPLOYMENT UPDATION API***********/
    public function updateEmploymentDetails($id, Request $request)
    {
        $id=base64_decode($id);
        $employee = DB::table('proll_employee')->where('id', $id)->exists();
        if(!$employee) {
            return response()->json([
                'status' => 'failure',
                'data' => 'Invalid employee ID'
            ],400);
        }
        Employee::updateEmploymentDetails($id,$request);
        Employee::updateEmployeeSalary($id,$request);
        return response()->json([
            'status'=>'success',
            'message'=>'Employment detail updated successfully'

        ],200);

    }

    public function getApplicationLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_id' => 'required',
            'module_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                ],
                401);
        }

        $data=MultiApprovalHelpers::getApplicationLog($request->module_id,$request->application_id);
        if($data){
            return response()->json([
                'status'=>'success',
                'data'=>$data

            ],200);
        }else{
            return response()->json(
                [
                    'error'=>'No record found.',
                    'status'=>'failure',
                ],
                404);
        }
    }
    /***********END OF EMPLOYEE EMPLOYMENT UPDATION API***********/

    public function withdrawApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => 'required',
            'application_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                ],
                401);
        }
        $module=$request->module_id;
        $application_id=$request->application_id;
        if(Module::where(['id'=>$module])->exists()){
                $res=Module::withDrawApplication($module,$application_id);
                if($res){
                    return response()->json([
                        'status'=>'success',
                        'message'=>'Application has been withdrawal successfully'

                    ],200);
                }else{
                    return response()->json(
                        [
                            'error'=>'Invalid application ID.',
                            'status'=>'failure',
                        ],
                        401);
                }
        }else{
            return response()->json(
                [
                    'error'=>'Invalid module ID.',
                    'status'=>'failure',
                ],
                401);
        }

    }

    public function populateLeaveBankByEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                ],
                401);
        }
        $employee_id=$request->employee_id;
        Leave::populateLeaveBankByEmployee($employee_id);

        return response()->json([
            'status'=>'success',
            'message'=>'Leave bank updated successfully!'

        ],200);
    }

    public function populateLeaveBankBySetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'setup_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'error'=>$validator->errors(),
                    'status'=>'failure',
                ],
                401);
        }
        $setup_id=$request->setup_id;
        Leave::populateLeaveBankBySetup($setup_id);

        return response()->json([
            'status'=>'success',
            'message'=>'Leave bank updated successfully!'

        ],200);
    }

    public function geEmployeeYear(Request $request)
    {
        Employee::geEmployeeYear($request->empid);

    }
    public function geEmployeeDetails(EmployeeDetailsRequest $requestFields)
    {
       $requestfields = $requestFields->only(['email']);

       $emp = Employee::where('loginname','=',$requestfields["email"])->first();


       if(!$emp){
        return response()->json([
            'status'=>'failed',
            'message'=>"emp doesn't exist"

        ],200);
       }else{
        $e_pass_word = Hash::make($emp["pass_word"]);

        $name_salute_detail = proll_reference_data::wherehas('proll_reference_data_code',function($q){
        $q->where('reference_code','=','name_salutation');
        })->select('description',"reference_key","ref_id","id")->where('description','=',$emp->name_salute)->first();
        return response()->json([
            'status'=>'pass',
            'message'=>"emp available",
            "data"=>array('name_salute_detail'=>$name_salute_detail,'country'=>$emp->country_details,'designation'=>$emp->emp_designation,'password'=>$emp->pass_word,'e_pass_word'=>$e_pass_word,'loc_detail'=>$emp->emp_location,'cell_number'=>$emp->cell_number,'city'=>$emp->city,'name'=>$emp->name,'id'=>$emp->id,'empcode'=>$emp->empcode)

        ],200);

       }

    }
    public function getempcredentials(DownloadCredientialsRequest $requestfields){
        $emp_list = Employee::select("id","name","loginname","pass_word")->get();


 //       $status = (new FastExcel($emp_list))->export('sendcredentials.csv');

        if ($requestfields->hasFile('credientials_file')) {
            $file = $requestfields->file('credientials_file')->getRealPath();
            $data = $this->csvToArray(Employee::$mis_headers,$file);
            foreach($data as $emp){
              //email credientials to emp;
                $fetch_emp = Employee::where('empcode','=',$emp["empcode"])->first();
                //send email as per requirements
            }
        }
        return response()->json([
            'status'=>'success',
            'message'=>'send credidientials submit successfully',
            "data"=>$emp_list

        ],200);

    }
    public function managehr(Request $request){
        $proll_clients = proll_client_assist::get();
        if($proll_clients->isEmpty()){
            return response()->json([
                'status'=>'failed',
                'message'=>"proll client doesn't exist"
            ],200);
        }else{
            $proll_clients = ProllClientAssistResource::collection($proll_clients);
            return response()->json([
                'status'=>'pass',
                'message'=>"proll client list",
                "data"=>$proll_clients
            ],200);
        }
    }
    public function managehrdetail(Request $request){
        $client_assist_id = $request->{'client_assist_id'};

        $proll_client_assist = proll_client_assist::where('id','=',$client_assist_id)->first();

        if(!$proll_client_assist){
            return response()->json([
                'status'=>'failed',
                'message'=>"proll client doesn't exist"
            ],200);
        }else{
            $proll_client_assist = new ProllClientAssistResource($proll_client_assist);

            $roles = Role::select('id','role')->get();
            $name_salute_dropdown = proll_reference_data::wherehas('proll_reference_data_code',function($q){
                $q->where('reference_code','=','name_salutation');
            })->select('description',"reference_key","ref_id","id")->get();

            $emp_status_dropdown = proll_reference_data::wherehas('proll_reference_data_code',function($q){
                $q->where('reference_code','=','Job_Status');
            })->select('description',"reference_key","ref_id","id")->get();
            return response()->json([
                'status'=>'pass',
                'message'=>"proll client",
                "data"=>array("client_assis"=>$proll_client_assist,"account_status_dropdown"=>$emp_status_dropdown,"roles"=>$roles,"name_salute_dropdown"=>$name_salute_dropdown)
            ],200);
        }
    }
    public function addmanagehr(AddOrUpdateManageHrRequest $requestFields){
        $proll_client_assists = proll_client_assist::get();
        //check record existance
        if($proll_client_assists->isEmpty()){
            return response()->json(
                [
                    'status'=>'failed',
                    'message'=>'No client assist Availble'
                ],
                200);
        }
        else
        {

          $proll_client_assist = $requestFields->only(['user_name','city','auth_repre_name','off_address','country','off_phone','cell_number','name_salute','companyname','emp_id','role_id','status']);

          if ($requestFields->input('emp_id')) {
             $emp_id = $requestFields->input('emp_id');
              $emp = Employee::where('id','=',$emp_id)->first();
              if(!$emp){
                return response()->json(
                    [
                        'status'=>'failed',
                        'message'=>"Employee Doesn't Exist",
                    ],
                    200);
              }
           }
          $proll_client_assist['cid'] = 48;
          $proll_client_assist_password = $requestFields->only(['password']);

          $proll_client_assist['pass_word'] = $proll_client_assist_password["password"];
          $proll_client_assist['e_pass_word'] = Hash::make($proll_client_assist_password["password"]);

          $proll_client_assist_record = proll_client_assist::Create($proll_client_assist);
          //save in db
          $proll_client_assist_record->save();
          return response()->json(
              [
                  'status'=>'pass',
                  'message'=>'proll client asssist added successfully',
                  'data'=>$proll_client_assist
              ],
              200);

        }

    }

    public function updatemanagehr($client_assist_id,AddOrUpdateManageHrRequest $requestFields){
        $proll_client_assist_record_by_id = proll_client_assist::where('id','=',$client_assist_id)->first();


        if(!$proll_client_assist_record_by_id){
            return response()->json([
                'status'=>'failed',
                'message'=>"proll client doesn't exist"
            ],200);
        }else{
            $proll_client_assist = $requestFields->only(['user_name','city','off_address','auth_repre_name','country','off_phone','cell_number','name_salute','companyname','emp_id','role_id','status']);

            if ($requestFields->input('emp_id')) {
               $emp_id = $requestFields->input('emp_id');
                $emp = Employee::where('id','=',$emp_id)->first();
                if(!$emp){
                  return response()->json(
                      [
                          'status'=>'failed',
                          'message'=>"Employee Doesn't Exist",
                      ],
                      200);
                }
             }

            $proll_client_assist['cid'] = 48;
            $proll_client_assist_password = $requestFields->only(['password']);
            $proll_client_assist['pass_word'] = $proll_client_assist_password["password"];
            $proll_client_assist['e_pass_word'] = Hash::make($proll_client_assist_password["password"]);

            $proll_client_assist_record = proll_client_assist::where('id','=',$client_assist_id)->update($proll_client_assist);
            //save in db
            $theUpdatedrecord = $proll_client_assist_record_by_id->refresh();

            return response()->json(
                [
                    'status'=>'pass',
                    'message'=>'proll client asssist added successfully',
                    'data'=>$theUpdatedrecord
                ],
                200);

        }
    }
    public function destroyManager(ProllClientAssistDeleteRequest $requestfields)
    {
        $ids = $requestfields->input('ids');

        foreach($ids as $id)
        {
            $proll_client_assist_record = proll_client_assist::where('id', $id)->first();
            if(!$proll_client_assist_record)
            {
                return response()->json(
                            [
                                'status'=>'failed',
                                'message'=>"proll client doesn't exist"
                            ],
                            200);
            }
            $deleted_record = proll_client_assist::where('id', $id)->first()->delete();

        }


        return response()->json(
            [
                'status'=>'pass',
                'message'=>'success',
                'data'=>'proll client assist deleted successfully'
            ],
            200);

    }


}
