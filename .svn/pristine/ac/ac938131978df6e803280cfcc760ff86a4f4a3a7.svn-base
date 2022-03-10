<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'proll_employee';
    protected $primaryKey = 'id';

    use  Notifiable ,HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function UserAttendanceDetails($user_id,$date){


        $attendance_record_time_in=
             DB::table('al_roster')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('proll_client_location', 'proll_employee.loc_id', '=', 'proll_client_location.loc_id')
            ->leftJoin('emp_geo_attendance', 'emp_geo_attendance.attendance_id', '=', 'al_roster.roster_id')
            ->leftJoin('emp_geo_transactions', 'emp_geo_transactions.id', '=', 'emp_geo_attendance.emp_geo_transaction_id')
            ->where('al_roster.emp_id','=',$user_id)
            ->where('al_roster.roster_date','=',$date)
            ->select(
                'al_roster.plan_shift_time_in',
                'al_roster.plan_shift_time_out',
                'al_roster.actual_shift_time_in',
                'al_roster.actual_shift_time_out',
                DB::raw('(CASE WHEN al_roster.geo_attendance IS NOT NULL THEN al_roster.geo_attendance ELSE 0 END) as mobile_attendance'),
        'emp_geo_attendance.address as time_in_address','emp_geo_attendance.ontime','emp_geo_attendance.at_office as time_in_at_office',
        DB::raw('(CASE WHEN emp_geo_attendance.address IS NOT NULL THEN emp_geo_attendance.address ELSE proll_client_location.address END) as time_in_address'),
        DB::raw('(CASE WHEN emp_geo_attendance.server_time IS NOT NULL THEN emp_geo_attendance.server_time ELSE TIME(al_roster.actual_shift_time_in) END) as time_in_server_time'),
        DB::raw('(CASE WHEN emp_geo_attendance.remote_time IS NOT NULL THEN emp_geo_attendance.remote_time ELSE TIME(al_roster.actual_shift_time_in) END) as time_in_remote_time'),
        DB::raw('(CASE WHEN emp_geo_transactions.longitude IS NOT NULL THEN emp_geo_transactions.longitude ELSE proll_client_location.longitude END) as time_in_longitude'),
        DB::raw('(CASE WHEN emp_geo_transactions.latitude IS NOT NULL THEN emp_geo_transactions.latitude ELSE proll_client_location.latitude END) as time_in_latitude'),
        DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",proll_employee.picture) AS profile')
        )->addSelect(DB::raw("'present' as status"))

         ->orderBy('emp_geo_attendance.id', 'asc')
            ->first();


        $attendance_record_time_out= DB::table('al_roster')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('proll_client_location', 'proll_employee.loc_id', '=', 'proll_client_location.loc_id')
            ->leftJoin('emp_geo_attendance', 'emp_geo_attendance.attendance_id', '=', 'al_roster.roster_id')
            ->leftJoin('emp_geo_transactions', 'emp_geo_transactions.id', '=', 'emp_geo_attendance.emp_geo_transaction_id')
            ->where('al_roster.emp_id','=',$user_id)
            ->where('al_roster.roster_date','=',$date)
            ->where('al_roster.actual_shift_time_out','!=','0000-00-00 00:00:00')
            ->select('emp_geo_attendance.at_office as time_out_at_office',
                DB::raw('(CASE WHEN emp_geo_transactions.address IS NOT NULL THEN emp_geo_transactions.address ELSE proll_client_location.address END) as time_out_address'),
                DB::raw('(CASE WHEN emp_geo_attendance.server_time IS NOT NULL THEN emp_geo_attendance.server_time ELSE TIME(al_roster.actual_shift_time_out) END) as time_out_server_time'),
                DB::raw('(CASE WHEN emp_geo_attendance.remote_time IS NOT NULL THEN emp_geo_attendance.remote_time ELSE TIME(al_roster.actual_shift_time_out) END) as time_out_remote_time'),
                DB::raw('(CASE WHEN emp_geo_transactions.longitude IS NOT NULL THEN emp_geo_transactions.longitude ELSE proll_client_location.longitude END) as time_out_longitude'),
                DB::raw('(CASE WHEN emp_geo_transactions.latitude IS NOT NULL THEN emp_geo_transactions.latitude ELSE proll_client_location.latitude END) as time_out_latitude'))
             ->orderBy('emp_geo_attendance.id', 'desc')
            ->first();


        if($attendance_record_time_out){
            $attendance_record_time_in->time_out_address=$attendance_record_time_out->time_out_address;
            //            $attendance_record_time_in->time_out_at_office=$attendance_record_time_out->time_out_at_office;
            $attendance_record_time_in->time_out_at_office=0;
            $attendance_record_time_in->time_in_at_office=0;
            $attendance_record_time_in->time_out_server_time=$attendance_record_time_out->time_out_server_time;
            $attendance_record_time_in->time_out_remote_time=$attendance_record_time_out->time_out_remote_time;
            $attendance_record_time_in->time_out_longitude=$attendance_record_time_out->time_out_longitude;
            $attendance_record_time_in->time_out_latitude=$attendance_record_time_out->time_out_latitude;
            $attendance_records= $attendance_record_time_in;

        }else
        {

            $attendance_records=$attendance_record_time_in;
        }

        if($attendance_records){

                if ($attendance_records->actual_shift_time_in !='0000-00-00 00:00:00') {

                   if($attendance_records->actual_shift_time_out =='0000-00-00 00:00:00')
                   {
                       $attendance_records->time_out_address=null;
                       $attendance_records->time_out_at_office=null;
                       $attendance_records->time_out_server_time=null;
                       $attendance_records->time_out_remote_time=null;
                       $attendance_records->time_out_longitude=null;
                       $attendance_records->time_out_latitude=null;
                   }


                    return $attendance_records;
                }else
                {


                    $holiday_check= DB::table('holidays as h')
                        ->where('h.start_date','<=',$date)
                        ->where('h.end_date','>=',$date)
                        ->exists();
                    if($holiday_check){
                          return $attendance_records = DB::table('proll_employee')
                            ->where('id', '=',$user_id)
                            ->select(
                                DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                            )->addSelect(DB::raw("'Holiday' as status"))
                            ->first();
                    }else{
                        $leave= DB::table('proll_leave as l')
                            ->leftjoin('proll_leave_type_c as lt','l.type_id','=','lt.id')
                            ->where('empid',$user_id)
//                            ->where('fdate',$date)
                            ->whereDate('fdate', '<=',$date)
                            ->whereDate('tdate', '>=',$date)
                            ->select('lt.name','l.nod')->first();
                        if($leave){
                            $nod=$leave->nod;
                            $leave_type=str_replace(' Leave','',$leave->name);// remove the word leave if already exit.
                            if($nod==0.25){$nod_text="Short";}elseif($nod==0.5){$nod_text="Half";}else{$nod_text="";};
                            $leave_type.=' Leave';
                            if($nod_text){
                                $leave_type=$nod_text.' '.$leave_type;
                            }
                            return  $attendance_records = DB::table('proll_employee')
                                ->where('id', '=',$user_id)
                                ->select(
                                    DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                                )->addSelect(DB::raw("'$leave_type' as status"))
                                ->first();
                        }else
                        {


                            if(strtotime(date('Y-m-d'))==strtotime($date) && strtotime(date('Y-m-d H:i:s'))<strtotime($attendance_record_time_in->plan_shift_time_in)){
                                return  $attendance_records =DB::table('proll_employee')
                                    ->where('id', '=',$user_id)
                                    ->select(
                                        DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                                    )->addSelect(DB::raw("'Office is closed' as status"))
                                    ->first();
                            }elseif(strtotime(date('Y-m-d'))<strtotime($date)){
                                return  $attendance_records =DB::table('proll_employee')
                                    ->where('id', '=',$user_id)
                                    ->select(
                                        DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                                    )->addSelect(DB::raw("'N/A' as status"))
                                    ->first();
                            }else{
                                return  $attendance_records =DB::table('proll_employee')
                                    ->where('id', '=',$user_id)
                                    ->select(
                                        DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                                    )->addSelect(DB::raw("'Absent' as status"))
                                    ->first();
                            }

                        }
                    }


                }
        }
        else
        {
            return  $attendance_records = DB::table('proll_employee')
                ->where('id', '=',$user_id)
                ->select(
                    DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                )->addSelect(DB::raw("'Holiday' as status"))
                ->first();
        }


    }

    public static function UserAttendanceDetailsByManager($user_id,$date)
    {
//         DB::enableQueryLog();
        $user_ids=encrypt($user_id);
        $attendance_record_time_in= DB::table('al_roster')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('proll_client_location', 'proll_employee.loc_id', '=', 'proll_client_location.loc_id')
            ->leftJoin('emp_geo_attendance', 'emp_geo_attendance.attendance_id', '=', 'al_roster.roster_id')
            ->leftJoin('emp_geo_transactions', 'emp_geo_transactions.id', '=', 'emp_geo_attendance.emp_geo_transaction_id')
            ->where('al_roster.emp_id','=',$user_id)
            ->where('al_roster.roster_date','=',$date)
            ->select('proll_employee.name','al_roster.actual_shift_time_in','al_roster.plan_shift_time_in',
                'al_roster.actual_shift_time_out' ,'al_roster.plan_shift_time_out' ,
                'al_roster.geo_attendance as mobile_attendance',
                DB::raw('(CASE WHEN emp_geo_attendance.address IS NOT NULL THEN emp_geo_attendance.address ELSE proll_client_location.address END) as time_in_address'),
                'emp_geo_attendance.ontime',
                DB::raw('TIME(al_roster.actual_shift_time_in) as time_in_at_office'),
                DB::raw('(CASE WHEN emp_geo_attendance.server_time IS NOT NULL THEN emp_geo_attendance.server_time ELSE TIME(al_roster.actual_shift_time_in) END) as time_in_server_time'),
                DB::raw('(CASE WHEN emp_geo_attendance.remote_time IS NOT NULL THEN emp_geo_attendance.remote_time ELSE TIME(al_roster.actual_shift_time_in) END) as time_in_remote_time'),
                DB::raw('(CASE WHEN emp_geo_transactions.longitude IS NOT NULL THEN emp_geo_transactions.longitude ELSE proll_client_location.longitude END) as time_in_longitude'),
                DB::raw('(CASE WHEN emp_geo_transactions.latitude IS NOT NULL THEN emp_geo_transactions.latitude ELSE proll_client_location.latitude END) as time_in_latitude'),
                'emp_geo_attendance.at_office',
                DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",proll_employee.picture) AS profile')
            )->addSelect(DB::raw("'present' as status"))
             ->addSelect(DB::raw("'$user_ids' as user_id"))
            ->orderBy('emp_geo_attendance.id', 'DESC')
            ->first();


//            $query = DB::getQueryLog();
//            print_r($query);
//            die;
// ==   dd($attendance_record_time_in);
        $attendance_record_time_out= DB::table('al_roster')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('proll_client_location', 'proll_employee.loc_id', '=', 'proll_client_location.loc_id')
            ->leftJoin('emp_geo_attendance', 'emp_geo_attendance.attendance_id', '=', 'al_roster.roster_id')
            ->leftJoin('emp_geo_transactions', 'emp_geo_transactions.id', '=', 'emp_geo_attendance.emp_geo_transaction_id')
            ->where('al_roster.emp_id','=',$user_id)
            ->where('al_roster.roster_date','=',$date)
            ->select('proll_employee.name',
                DB::raw('(CASE WHEN emp_geo_attendance.address IS NOT NULL THEN emp_geo_attendance.address ELSE proll_client_location.address END) as time_out_address'),
                DB::raw('TIME(al_roster.actual_shift_time_out) as time_out_at_office'),
                DB::raw('(CASE WHEN emp_geo_attendance.server_time IS NOT NULL THEN emp_geo_attendance.server_time ELSE TIME(al_roster.actual_shift_time_out) END) as time_out_server_time'),
                DB::raw('(CASE WHEN emp_geo_attendance.remote_time IS NOT NULL THEN emp_geo_attendance.remote_time ELSE TIME(al_roster.actual_shift_time_out) END) as time_out_remote_time'),
                DB::raw('(CASE WHEN emp_geo_transactions.longitude IS NOT NULL THEN emp_geo_transactions.longitude ELSE proll_client_location.longitude END) as time_out_longitude'),
                DB::raw('(CASE WHEN emp_geo_transactions.latitude IS NOT NULL THEN emp_geo_transactions.latitude ELSE proll_client_location.latitude END) as time_out_latitude'),
                'emp_geo_attendance.at_office'
            )
            ->addSelect(DB::raw("'$user_ids' as user_id"))
            ->orderBy('emp_geo_attendance.id', 'ASC')
            ->first();

                /*if($attendance_record_time_out){
                    $attendance_record_time_in->time_out_address= "C1, Jehlum Block, Green Forts 2,lahore";
                    $attendance_record_time_in->time_in_address= "C1, Jehlum Block, Green Forts 2,lahore";

                    $attendance_record_time_in->time_in_longitude="74.1973905";
                    $attendance_record_time_in->time_in_latitude="31.4349703";
                    $attendance_record_time_in->time_out_longitude="74.1973905";
                    $attendance_record_time_in->time_out_latitude="31.4349703";
                    $attendance_records=$attendance_record_time_in;
                }else
                {
                    $attendance_records=$attendance_record_time_in;
                }*/

        $attendance_records='';
if($attendance_record_time_in) {
    $attendance_record_time_in->time_in_address = (isset($attendance_record_time_in->time_in_address) ? $attendance_record_time_in->time_in_address : '123');
//                    $attendance_record_time_in->time_in_at_office=($attendance_record_time_in->time_in_at_office?$attendance_record_time_in->time_in_at_office:'');
    $attendance_record_time_in->time_in_at_office = 0;
    $attendance_record_time_in->time_in_server_time = ($attendance_record_time_in->time_in_server_time ? $attendance_record_time_in->time_in_server_time : '');
    $attendance_record_time_in->time_in_remote_time = ($attendance_record_time_in->time_in_remote_time ? $attendance_record_time_in->time_in_remote_time : '');
    $attendance_record_time_in->time_in_longitude = ($attendance_record_time_in->time_in_longitude ? $attendance_record_time_in->time_in_longitude : '');
    $attendance_record_time_in->time_in_latitude = ($attendance_record_time_in->time_in_latitude ? $attendance_record_time_in->time_in_latitude : '');
    $attendance_record_time_in->at_office = ($attendance_record_time_in->at_office ? $attendance_record_time_in->at_office : 0);
    $attendance_records = $attendance_record_time_in;
}
            if($attendance_record_time_in && $attendance_record_time_in->mobile_attendance !=1){
                    $attendance_record_time_in->time_out_address = ($attendance_record_time_out->time_out_address ? $attendance_record_time_out->time_out_address : '');
                    $attendance_record_time_in->time_out_at_office = ($attendance_record_time_out->time_out_at_office ? $attendance_record_time_out->time_out_at_office : '');
                    $attendance_record_time_in->time_out_at_office = 0;
                    $attendance_record_time_in->time_out_server_time = ($attendance_record_time_out->time_out_server_time ? $attendance_record_time_out->time_out_server_time : '');
                    $attendance_record_time_in->time_out_remote_time = ($attendance_record_time_out->time_out_remote_time ? $attendance_record_time_out->time_out_remote_time : '');
                    $attendance_record_time_in->time_out_longitude = ($attendance_record_time_out->time_out_longitude ? $attendance_record_time_out->time_out_longitude : '');
                    $attendance_record_time_in->time_out_latitude = ($attendance_record_time_out->time_out_latitude ? $attendance_record_time_out->time_out_latitude : '');
                $attendance_record_time_in->mobile_attendance=0;


                $attendance_time = $attendance_record_time_in->actual_shift_time_in;

                $shift_time1=  Carbon::parse($attendance_record_time_in->plan_shift_time_in)->addMinute(10);
                $shift_time2=  Carbon::parse($attendance_record_time_in->plan_shift_time_in)->addMinute(30);

                if($attendance_time <=$shift_time1)
                {
                    $ontime=0;
                }elseif ($attendance_time <=$shift_time2)
                {
                    $ontime=1;
                }else{
                    $ontime=2;
                }
                $attendance_record_time_in->ontime=$ontime;

                $attendance_records= $attendance_record_time_in;
            }



        if ($attendance_records) {

        if ($attendance_records->actual_shift_time_in != "0000-00-00 00:00:00") {
            if ($attendance_records->actual_shift_time_out == "0000-00-00 00:00:00") {

                $attendance_transactions = DB::table('emp_geo_transactions')
                    ->where('emp_id', '=', $user_id)
                    ->whereDate('created_at', '=', $date)
                    ->select('server_time', 'remote_time', 'longitude', 'latitude','address')
                    ->orderByDesc('id')
                    ->first();


                if ($attendance_transactions) {
                    $attendance_records->time_out_address = $attendance_transactions->address;
                    $attendance_records->time_out_server_time = $attendance_transactions->server_time;
                    $attendance_records->time_out_remote_time = $attendance_transactions->remote_time;
                    $attendance_records->time_out_longitude = $attendance_transactions->longitude;
                    $attendance_records->time_out_latitude = $attendance_transactions->latitude;
                    return $attendance_records;
                } else {
                    return $attendance_records;
                }

            } else {
                return $attendance_records;
            }

        } else {


               $leave_check= DB::table('proll_leave')
                ->where('empid',$user_id)
                ->where('fdate',$date)
                ->exists();

               if($leave_check){
                   return  $attendance_records = DB::table('proll_employee')
                           ->where('id', '=',$user_id)
                           ->select('proll_employee.name',
                                    DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                           )->addSelect(DB::raw("'Leave' as status"))
                           ->addSelect(DB::raw("'$user_ids' as user_id"))
                           ->first();
               }else
               {
                   return  $attendance_records =DB::table('proll_employee')
                           ->where('id', '=',$user_id)
                           ->select('proll_employee.name',
                               DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                           )->addSelect(DB::raw("'Absent' as status"))
                           ->addSelect(DB::raw("'$user_ids' as user_id"))
                           ->first();
               }

        }
    }else
        {

          return  $attendance_records = DB::table('proll_employee')
                ->where('id', '=',$user_id)
                ->select('proll_employee.name',
                   DB::raw('CONCAT("'.env('BASE_URL').'/emp_pictures/",picture) AS profile')
                )->addSelect(DB::raw("'Holiday' as status"))
                 ->addSelect(DB::raw("'$user_ids' as user_id"))
                ->first();
        }



    }



    public static function UserAttendanceListDetails($user_id,$date,$role_id,$cid){
        $attendance=array();
        if ($role_id==2){

                // $role_exit= DB::table('user_roles')
                // ->where('user_id','=',$user_id)
                // ->where('role_id','=',$role_id)
                // ->exists();
           $role_exit = DB::table('user_roles')
            ->join('group_roles', 'group_roles.id', '=', 'user_roles.group_role_id')
            ->join('roles', 'roles.id', '=', 'group_roles.roles_portal_id')
            ->where('user_roles.cid', $cid)
            ->where('user_roles.user_id', $user_id)
            ->exists();
            // dd($role_exit);
         if($role_exit){
          $reporting_list=Employee::ReportingUserInfo($user_id);

             foreach ($reporting_list as $user)
             {
                 $res=User::UserAttendanceDetailsByManager($user->id,$date);
                 if(isset($res->actual_shift_time_in) || isset($res->actual_shift_time_out)) {
                     $attendance[] = $res;
                 }

             }
             return $attendance;
         }


        }elseif($role_id==3 || $role_id==4)
        {
            $role_exit = DB::table('user_roles')
            ->join('group_roles', 'group_roles.id', '=', 'user_roles.group_role_id')
            ->join('roles', 'roles.id', '=', 'group_roles.roles_portal_id')
            ->where('user_roles.cid', $cid)
            ->where('user_roles.user_id', $user_id)
            ->exists();

            // $role_exit= DB::table('user_roles')
            //     ->where('user_id','=',$user_id)
            //     ->where('role_id','=',$role_id)
            //     ->exists();
            // dd($role_exit);
            if($role_exit)
            {
                $user_ids= Employee::where('cid',$cid)
                    ->where('status',1)
                    ->select('id')
                    ->get();

                foreach ($user_ids as $user)
                {
                    $res=User::UserAttendanceDetailsByManager($user->id,$date);
                    if(isset($res->actual_shift_time_in) || isset($res->actual_shift_time_out)) {
                        $attendance[] = $res;
                    }
                }
                return $attendance;

            }



        }else{
            return 0;
        }


    }
// get reporting to information by user ID
    public static function UserInformationAndReportiingTO($user_id){
//        DB::enableQueryLog();
        $user= DB::table('proll_employee')
        ->join('proll_department_managers', 'proll_department_managers.id', '=', 'proll_employee.reporting_to_id')
        ->join('department_hierarchy', 'department_hierarchy.id', '=', 'proll_department_managers.department_hierarchy_id')
        ->where('proll_employee.id', '=', $user_id)
        ->select('proll_employee.name','proll_department_managers.line_manager','proll_employee.cid','proll_department_managers.email')
        ->first();

        if(empty($user)){
            $user= DB::table('proll_employee')
                ->join('proll_department_managers', 'proll_department_managers.id', '=', 'proll_employee.dept_id')
                ->join('department_hierarchy', 'department_hierarchy.id', '=', 'proll_department_managers.department_hierarchy_id')
                ->where('proll_employee.id', '=', $user_id)
                ->select('proll_employee.name','proll_department_managers.line_manager','proll_employee.cid','proll_department_managers.email')
                ->first();

        }

//        $query = DB::getQueryLog();
//        print_r($query);
//        die;
//        dd($user);
        return $user;
    }
    public static function getFiscalYearDatesByModule($module_name){
        return $data = DB::table('proll_dates')
            ->where('form', $module_name)->first();

        $cdate=date("Y-m-d");
        $cyear=date("Y",strtotime($cdate)); // current year
        $from=date($cyear.'-m-d',strtotime($data->mini));
        $to=date($cyear.'-m-d',strtotime($data->maxi));

        if(strtotime($cdate)<strtotime($from)){
            $from=date('Y-m-d',strtotime($from.' -1 year'));
        }
        if(strtotime($cdate)>strtotime($to)){
            $to=date('Y-m-d',strtotime($to.' +1 year'));
        }
        $data->mini=$from;
        $data->maxi=$to;


    }

    public static function isDisclaimerAccepted($user_id,$cid){
         $res= DB::table('proll_employee')
            ->where('id', $user_id)
            ->where('cid', $cid)
            ->pluck('disclaimer_accepted');
         if($res){
             return $res[0];
         }else{
             return false;
         }
    }
    public static function disclaimerAccepted($user_id,$cid){
         DB::table('proll_employee')
            ->where('id', $user_id)
            ->where('cid', $cid)
             ->update([
                 'disclaimer_accepted_at' => Carbon::now(),
                 'disclaimer_accepted' => '1'
             ]);
    }


}
