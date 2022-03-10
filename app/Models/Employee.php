<?php

namespace App\Models;

use App\Helpers\MultiApprovalHelpers;
use App\Models\User;
use App\Services\PayUService\Exception;
use Carbon\CarbonPeriod;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{

    protected $table = 'proll_employee';
    //fillable coumns
    protected $fillable = [
        'loginname', 'status', 'name', 'is_active_type',
    ];
    protected $hidden = array('department_id');

    public function leave($user_id, $status)
    {

        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $profile = Employee::where('id', $user_id)->value('picture');
        if ($status == 1) {
            return $this->hasMany('App\Leave', 'empid', 'id')
                ->join('proll_leave_type_c', 'type_id', '=', 'proll_leave_type_c.id')
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->where(function ($query) use ($status) {
                    $query->where('appstatus', $status)
                        ->orwhere('appstatus', 4);
                })
                ->select('leaveid', 'type_id', 'emp_date as added_date', 'fdate as from_date', 'tdate as to_date', 'nod as total_days', 'appstatus', 'proll_leave_type_c.type', 'proll_leave.emp_date as receivedDate ', DB::raw("cast(view_status as unsigned) as 'view_status'"), DB::raw("(CASE WHEN nod = 0.25 THEN 'short' WHEN nod = 0.5 THEN 'half' ELSE 'full' END) as sub_type"), DB::raw("(CASE WHEN '$profile' IS NOT NULL THEN 'https://www.peoplei.tech/people/emp_pictures/$profile' ELSE 'https://www.peoplei.tech/people/emp_pictures/favicon.png' END) AS profile")
                )->orderBy('leaveid', 'DESC');
        } else {
            return $this->hasMany('App\Leave', 'empid', 'id')
                ->join('proll_leave_type_c', 'type_id', '=', 'proll_leave_type_c.id')
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->where('appstatus', $status)
                ->select('leaveid', 'type_id', 'emp_date as added_date', 'fdate as from_date', 'tdate as to_date', 'nod as total_days', 'appstatus', 'proll_leave_type_c.type', 'proll_leave.emp_date as receivedDate ', DB::raw("cast(view_status as unsigned) as 'view_status'"), DB::raw("(CASE WHEN nod = 0.25 THEN 'short' WHEN nod = 0.5 THEN 'half' ELSE 'full' END) as sub_type"), DB::raw("(CASE WHEN '$profile' IS NOT NULL THEN 'https://www.peoplei.tech/people/emp_pictures/$profile' ELSE 'https://www.peoplei.tech/people/emp_pictures/favicon.png' END) AS profile")
                )->orderBy('leaveid', 'DESC');
        }
    }

    public function leave_v2_3($user_id, $status)
    {

        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $profile = Employee::where('id', $user_id)->value('picture');
        return $this->hasMany('App\Leave', 'empid', 'id')
            ->join('proll_leave_type_c', 'type_id', '=', 'proll_leave_type_c.id')
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->when($status, function ($query) use ($status) {
                if ($status == 1) {
                    return $query->where('appstatus', '!=', 3)
                        ->where('hr_status', '!=', 3);
                }
            })
            ->where(function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('appstatus', $status)
                        ->orwhere('hr_status', $status)
                        ->orwhere('appstatus', 4)
                        ->orwhere('hr_status', 4);
                } elseif ($status == 2) {
                    $query->where('appstatus', $status)
                        ->where('hr_status', $status);
                } elseif ($status == -1) {

                } else {
                    $query->where('appstatus', $status)
                        ->orwhere('hr_status', $status);
                }

            })
            ->select('leaveid as leave_id', 'type_id as leave_type_id', 'emp_date as added_date', 'fdate as from_date', 'tdate as to_date',
                'from_time', 'to_time', 'nod as total_days', 'appstatus as lm_status', 'hr_status', 'leave_violation',
                'proll_leave_type_c.type as leave_type', 'proll_leave.emp_date as receivedDate ',
                DB::raw("cast(view_status as unsigned) as 'view_status'"),
                DB::raw("(CASE WHEN nod = 0.25 THEN 'short' WHEN nod = 0.5 THEN 'half' ELSE 'full' END) as sub_type"),
                DB::raw("(CASE WHEN '$profile' IS NOT NULL THEN '" . env('BASE_URL') . "/emp_pictures/$profile' ELSE '" . env('BASE_URL') . "/emp_pictures/favicon.png' END) AS profile")
            )->orderBy('leaveid', 'DESC');

    }
//   get leave detail - leave phase 2
    public function leave_p2($user_id, $status)
    {

        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $profile = Employee::where('id', $user_id)->value('picture');
        return $this->hasMany('App\Leave', 'empid', 'id')
            ->join('hr_leave_setup as s', 'type_id', '=', 's.id')
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->when($status, function ($query) use ($status) {
                if ($status == 1) {
                    return $query->where('appstatus', '!=', 3)
                        ->where('hr_status', '!=', 3);
                }
            })
            ->where(function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('appstatus', $status)
                        ->orwhere('hr_status', $status)
                        ->orwhere('appstatus', 4)
                        ->orwhere('hr_status', 4);
                } elseif ($status == 2) {
                    $query->where('appstatus', $status)
                        ->where('hr_status', $status);
                } elseif ($status == -1) {

                } else {
                    $query->where('appstatus', $status)
                        ->orwhere('hr_status', $status);
                }

            })
            ->select('leaveid as leave_id', 'type_id as leave_type_id', 'emp_date as added_date', 'fdate as from_date', 'tdate as to_date',
                'from_time', 'to_time', 'nod as total_days', 'appstatus as lm_status', 'hr_status', 'leave_violation',
                's.leave_title as leave_type', 'proll_leave.emp_date as receivedDate ',
                DB::raw("cast(view_status as unsigned) as 'view_status'"),
                DB::raw("(CASE WHEN nod = 0.25 THEN 'short' WHEN nod = 0.5 THEN 'half' ELSE 'full' END) as sub_type"),
                DB::raw("(CASE WHEN '$profile' IS NOT NULL THEN '" . env('BASE_URL') . "/emp_pictures/$profile' ELSE '" . env('BASE_URL') . "/emp_pictures/favicon.png' END) AS profile")
            )->orderBy('leaveid', 'DESC');

    }

    public static function expense($id, $status)
    {

        $dates = User::getFiscalYearDatesByModule('Expense Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        if ($status == 3) {
            return DB::table('proll_expense')
                ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
                ->join('proll_expense_detail', 'proll_expense_detail.exp_id', '=', 'proll_expense.id')
                ->join('proll_currency', 'proll_expense.currency_id', '=', 'proll_currency.id')
                ->join('proll_expense_type', 'proll_expense_type.id', '=', 'proll_expense_detail.type_id')
                ->where('proll_expense.eid', '=', $id)
                ->where(function ($query) use ($status) {
                    $query->where('proll_expense.lm_status', '=', $status);
                    $query->orwhere('proll_expense.hr_status', '=', $status);
                })
                ->where('proll_expense.exp_date', '>=', $min_date)
                ->where('proll_expense.exp_date', '<=', $max_date)
                ->select(
                    'proll_expense.id as expense_id', 'proll_expense_type.type', 'proll_expense.added_on', 'proll_expense_detail.exp_date', DB::raw('SUM(proll_expense_detail.amount) as amount'), 'proll_currency.symbol', 'proll_expense.hr_status', DB::raw("cast(proll_expense.view_status as unsigned) as 'view_status'"), 'proll_expense.lm_status as lm_status', DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
//                DB::raw("CONCAT('".$_ENV['BASE_URL']."'/emp_pictures/',proll_employee.picture) AS picture")
                )
                ->groupBy(DB::raw('proll_expense.id, proll_expense.eid,proll_expense_type.id,proll_expense_detail.id'))
                ->orderBy('proll_expense.id', 'DESC')
                ->paginate(10);
        } else {
            if ($status == 1) {
                return DB::table('proll_expense')
                    ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
                    ->join('proll_expense_detail', 'proll_expense_detail.exp_id', '=', 'proll_expense.id')
                    ->join('proll_currency', 'proll_expense.currency_id', '=', 'proll_currency.id')
                    ->join('proll_expense_type', 'proll_expense_type.id', '=', 'proll_expense_detail.type_id')
                    ->where('proll_expense.eid', '=', $id)
                    ->where(function ($query) use ($status) {

                        $query->where('proll_expense.hr_status', '=', $status)
                            ->orwhere('proll_expense.lm_status', 4)
                            ->orwhere('proll_expense.hr_status', 4);
                    })
                    ->where('proll_expense.hr_status', '!=', 3)
                    ->where('proll_expense.lm_status', '!=', 3)
                    ->where('proll_expense.exp_date', '>=', $min_date)
                    ->where('proll_expense.exp_date', '<=', $max_date)
                    ->select(
                        'proll_expense.id as expense_id', 'proll_expense_type.type', 'proll_expense.added_on', 'proll_expense_detail.exp_date', DB::raw('SUM(proll_expense_detail.amount) as amount'), 'proll_currency.symbol', 'proll_expense.hr_status', DB::raw("cast(proll_expense.view_status as unsigned) as 'view_status'"), 'proll_expense.lm_status as lm_status', DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
//                DB::raw("CONCAT('".$_ENV['BASE_URL']."'/emp_pictures/',proll_employee.picture) AS picture")
                    )
                    ->groupBy(DB::raw('proll_expense.id, proll_expense.eid,proll_expense_type.id,proll_expense_detail.id'))
                    ->orderBy('proll_expense.id', 'DESC')
                    ->paginate(10);
            } else {
                return DB::table('proll_expense')
                    ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
                    ->join('proll_expense_detail', 'proll_expense_detail.exp_id', '=', 'proll_expense.id')
                    ->join('proll_currency', 'proll_expense.currency_id', '=', 'proll_currency.id')
                    ->join('proll_expense_type', 'proll_expense_type.id', '=', 'proll_expense_detail.type_id')
                    ->where('proll_expense.eid', '=', $id)
                    ->where('proll_expense.hr_status', '=', $status)
                    ->where('proll_expense.hr_status', '!=', 3)
                    ->where('proll_expense.lm_status', '!=', 3)
                    ->where('proll_expense.exp_date', '>=', $min_date)
                    ->where('proll_expense.exp_date', '<=', $max_date)
                    ->select(
                        'proll_expense.id as expense_id', 'proll_expense_type.type', 'proll_expense.added_on', 'proll_expense_detail.exp_date', DB::raw('SUM(proll_expense_detail.amount) as amount'), 'proll_currency.symbol', 'proll_expense.hr_status', DB::raw("cast(proll_expense.view_status as unsigned) as 'view_status'"), 'proll_expense.lm_status as lm_status', DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
//                DB::raw("CONCAT('".$_ENV['BASE_URL']."'/emp_pictures/',proll_employee.picture) AS picture")
                    )
                    ->groupBy(DB::raw('proll_expense.id, proll_expense.eid,proll_expense_type.id,proll_expense_detail.id'))
                    ->orderBy('proll_expense.id', 'DESC')
                    ->paginate(10);
            }
        }

//        return DB::table('proll_expense')
        //            ->join('proll_expense_detail', 'proll_expense_detail.exp_id', '=', 'proll_expense.id')
        //            ->join('proll_expense_type', 'proll_expense_type.id', '=', 'proll_expense_detail.type_id')
        //            ->join('proll_currency', 'proll_currency.id', '=', 'proll_expense.currency_id')
        //            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
        //            ->join('proll_client_designation', 'proll_employee.designation', '=', 'proll_client_designation.designation_id')
        //            ->join('proll_department', 'proll_employee.dept_id', '=', 'proll_department.id')
        //            ->where('proll_expense.eid', '=', $id)
        //            ->where('proll_expense.hr_status', '=', $status)
        //            ->where('proll_expense.exp_date', '>=',$min_date)
        //            ->where('proll_expense.exp_date', '<=',$max_date)
        //            ->select('proll_employee.empcode','proll_employee.name','proll_client_designation.designation_name',
        //                'proll_department.department', 'proll_expense.id as expense_id','proll_expense_type.type','proll_expense.lm_status','proll_expense.hr_status','proll_expense.lm_comments','proll_expense.hr_comments',
        //                'proll_expense.added_on as expense_date','proll_expense_detail.amount','proll_currency.symbol',
        //                'proll_expense_detail.descr as reason',
        ////        DB::raw("CONCAT('".$_ENV['BASE_URL']."'/receipts/',proll_expense_detail.receipt) AS documents"),
        //                DB::raw('(CASE WHEN proll_expense_detail.receipt="" THEN null ELSE  CONCAT("'.env('BASE_URL').'/receipts/",proll_expense_detail.receipt) END) AS documents'),
        //                DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("'.env('BASE_URL').'/emp_pictures/",proll_employee.picture) ELSE "'.env('BASE_URL').'/emp_pictures/favicon.png" END) AS profile')
        //
        //            )
        //            ->get();
    }

    public static function expense_v2_3($id, $status)
    {

        $dates = User::getFiscalYearDatesByModule('Expense Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        return DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->join('proll_expense_detail', 'proll_expense_detail.exp_id', '=', 'proll_expense.id')
            ->join('proll_currency', 'proll_expense.currency_id', '=', 'proll_currency.id')
            ->join('proll_expense_type', 'proll_expense_type.id', '=', 'proll_expense_detail.type_id')
            ->where('proll_expense.eid', '=', $id)
            ->where('proll_expense.exp_date', '>=', $min_date)
            ->where('proll_expense.exp_date', '<=', $max_date)
            ->when($status, function ($query) use ($status) {
                if ($status == 1) {
                    return $query->where('lm_status', '!=', 3)
                        ->where('hr_status', '!=', 3);
                }
            })
            ->where(function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('lm_status', $status)
                        ->orwhere('hr_status', $status)
                        ->orwhere('lm_status', 4)
                        ->orwhere('hr_status', 4);
                } elseif ($status == 2) {
                    $query->where('lm_status', $status)
                        ->where('hr_status', $status);
                } else {
                    $query->where('lm_status', $status)
                        ->orwhere('hr_status', $status);
                }

            })
            ->select(
                'proll_expense.id as application_id', 'proll_expense_type.type', 'proll_expense.added_on',
                'proll_expense_detail.exp_date', DB::raw('SUM(proll_expense_detail.amount) as amount'),
                'proll_currency.symbol', 'proll_expense.hr_status', DB::raw("cast(proll_expense.view_status as unsigned) as 'view_status'"),
                'proll_expense.lm_status as lm_status',
                DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
            )
            ->groupBy(DB::raw('proll_expense.id, proll_expense.eid,proll_expense_type.id,proll_expense_detail.id'))
            ->orderBy('proll_expense.id', 'DESC')
            ->paginate(10);
    }

    public static function UserInformation($user_id)
    {

        return DB::table('proll_employee')
            ->join('proll_client_designation', 'proll_employee.designation', '=', 'proll_client_designation.designation_id')
            ->join('proll_department', 'proll_employee.dept_id', '=', 'proll_department.id')
            ->where('proll_employee.id', '=', $user_id)
            ->select(
                'proll_employee.name', DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("https://www.peoplei.tech/people/emp_pictures/",proll_employee.picture) ELSE "https://www.peoplei.tech/people/emp_pictures/favicon.png" END) AS picture'), 'proll_department.department', 'proll_department.id as department_id', 'proll_client_designation.designation_name', 'proll_employee.hom_address', 'proll_employee.cell_number', 'proll_employee.empcode'
            )
            ->first();
    }

    public static function UserInformation_v2_3($user_id)
    {
        return DB::table('proll_employee')
            ->leftjoin('proll_client_designation', 'proll_employee.designation', '=', 'proll_client_designation.designation_id')
            ->leftjoin('proll_department_managers as m', 'proll_employee.dept_id', '=', 'm.id')
            ->leftjoin('department_hierarchy as h', 'h.id', '=', 'm.department_hierarchy_id')
            ->where('proll_employee.id', '=', $user_id)
            ->select(
                'proll_employee.name',
                DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS picture'), 'h.department_name as department', 'h.id as department_id', 'proll_client_designation.designation_name', 'proll_employee.hom_address', 'proll_employee.cell_number', 'proll_employee.empcode'
            )->first();
    }

    // For HLS_2.3
    public static function ReportingUserInfo($user_id)
    {
        return DB::select('SELECT
                   DISTINCT(pe.id),
                    (
                    SELECT
                    (CASE WHEN proll_employee.id = pe.id THEN 0 ELSE proll_employee.id END) AS id
                    FROM proll_department_managers
                    INNER JOIN proll_employee ON proll_employee.loginname = proll_department_managers.email
                    WHERE proll_department_managers.id IN (
                    (CASE WHEN pe.reporting_to_id != 0 AND pe.reporting_to_id != pe.dept_id  THEN pe.reporting_to_id ELSE pe.dept_id END)
                    )
                    )
                    AS parent_id,
                    eb.band_desc,
                    pcd.designation_name,
                    pe.name,
                    pe.loginname,
                    pe.empcode,
                    (CASE WHEN pe.picture IS NOT NULL THEN concat("' . env('BASE_URL') . '/emp_pictures/",pe.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png"  END) as picture
                    FROM proll_employee pe
                    INNER JOIN proll_department_managers pdm ON (pdm.id = pe.reporting_to_id OR pdm.id = pe.dept_id)
                    LEFT JOIN employee_bands eb ON eb.id = pe.emp_band
                    LEFT JOIN proll_client_designation pcd ON pcd.designation_id = pe.designation
                    WHERE pe.status = 1 AND (
                    SELECT
                    (CASE WHEN proll_employee.id = pe.id THEN 0 ELSE proll_employee.id END) AS id

                    FROM proll_department_managers
                    INNER JOIN proll_employee ON proll_employee.loginname = proll_department_managers.email
                    WHERE proll_department_managers.id IN (
                    (CASE WHEN pe.reporting_to_id != 0 AND pe.reporting_to_id != pe.dept_id  THEN pe.reporting_to_id ELSE pe.dept_id END)
                    )
                    ) =' . $user_id . '
                    ORDER BY eb.band_desc ASC, parent_id ASC, pe.id ASC
                    ');
    }

    public static function ReportingUserInformation($user_id)
    {
        return DB::select('SELECT

                    eb.band_desc,
                    pcd.designation_name,
                    pe.name,
                    pe.loginname,
                    pe.empcode,
                     (CASE WHEN pe.picture IS NOT NULL THEN concat("' . env('BASE_URL') . '/emp_pictures/",pe.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png"  END) as picture
                    FROM proll_employee pe
                    INNER JOIN proll_department pd ON (pd.id = pe.reporting_to_id OR pd.id = pe.dept_id)
                    LEFT JOIN employee_bands eb ON eb.id = pe.emp_band
                    LEFT JOIN proll_client_designation pcd ON pcd.designation_id = pe.designation
                    WHERE pe.status = 1 AND (
                    SELECT
                    (CASE WHEN proll_employee.id = pe.id THEN 0 ELSE proll_employee.id END) AS id

                    FROM proll_department
                    INNER JOIN proll_employee ON proll_employee.loginname = proll_department.email
                    WHERE proll_department.id IN (
                    (CASE WHEN pe.reporting_to_id != 0 AND pe.reporting_to_id != pe.dept_id  THEN pe.reporting_to_id ELSE pe.dept_id END)
                    )
                    ) =' . $user_id . '
                    group by pe.id
                    ORDER BY eb.band_desc ASC, pe.id ASC
                    ');
    }
    /******* Get reporting users information according to selected reporting level*******/
    public static function ReportingUserInformation_v2_3($user_id, $client_id, $filter_by)
    {

        if ($filter_by == 'all') {
            $direct_report = '';
            $level = '';
        } elseif ($filter_by == 'second_level') {
            $direct_report = '';
            $level = 1;
        } else {
            $direct_report = 'selected';
            $level = 0;
        }
        $estatus = " AND e.status='1' ";
        $ids = array();
        $lm = MultiApprovalHelpers::get_lm_id($user_id);
        $employees_data = MultiApprovalHelpers::get_all_reporting_employees_with_all_columns($user_id, $lm->id, $keys = null, $estatus, $search = null, $sort_by = ' e.name', '', '', $direct_report, $level);
        foreach ($employees_data as $employee) {
            array_push($ids, $employee->id);
        }

        return DB::table('proll_employee as e')
            ->leftJoin('employee_bands as b', 'b.id', '=', 'e.emp_band')
            ->leftJoin('proll_client_designation as d', 'd.designation_id', '=', 'e.designation')
            ->whereIn('e.id', $ids)
            ->select('b.band_desc',
                'd.designation_name',
                'e.name',
                'e.loginname',
                'e.empcode',
                DB::raw('(CASE WHEN e.picture IS NOT NULL THEN concat("' . env('BASE_URL') . '/emp_pictures/",e.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png"  END) as picture'))
            ->get();
    }
    /******* END Get reporting users information according to selected reporting level*****/

    public static function GetUserListByHR($client_id)
    {
        return Employee::where('cid', $client_id)->pluck('id');
    }

    public static function mobile_information($user_id, $data)
    {

        $check_user_id = DB::table('mobile_application_details')->where('emp_id', '=', $user_id)->exists();
        if ($check_user_id) {
            $check_app_token = DB::table('mobile_application_details')
                ->where('app_token', '=', $data->app_token)
                ->where('emp_id', '=', $user_id)
                ->exists();
            if ($check_app_token) {
                DB::table('mobile_application_details')
                    ->where('app_token', $data->app_token)
                    ->where('emp_id', $user_id)
                    ->update([
                        'last_access_date' => Carbon::now(),
                        'login_status' => '1',
                        'token_status' => '1',
                        'updated_by' => $user_id,
                    ]);
            } else {

                DB::table('mobile_application_details')
                    ->insert([
                        'emp_id' => $user_id,
                        'os_type' => $data->os_type,
                        'app_version' => $data->app_version,
                        'app_token' => $data->app_token,
                        'token_status' => '1',
                        'login_status' => '1',
                        'os_version' => $data->os_version,
                        'last_access_date' => Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                    ]);
            }
        } else {

            DB::table('mobile_application_details')
                ->insert([
                    'emp_id' => $user_id,
                    'os_type' => $data->os_type,
                    'app_version' => $data->app_version,
                    'app_token' => $data->app_token,
                    'token_status' => '1',
                    'login_status' => '1',
                    'os_version' => $data->os_version,
                    'last_access_date' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'created_by' => $user_id,
                    'updated_by' => $user_id,
                ]);
        }
    }

    public static function EmployeeLogout($data)
    {
        $user_id = decrypt($data->user_id);
        $check_user_id = DB::table('mobile_application_details')->where('emp_id', '=', $user_id)->exists();
        if ($check_user_id) {
            $check_app_token = DB::table('mobile_application_details')->where('app_token', '=', $data->app_token)->exists();
            if ($check_app_token) {
                return DB::table('mobile_application_details')
                    ->where('app_token', $data->app_token)
                    ->where('emp_id', $user_id)
                    ->update([
                        'login_status' => '0',
                        'token_status' => '0',
                    ]);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function leave_notification($appstatus, $view_status, $fdate)
    {
        return $this->hasMany('App\Leave', 'empid', 'id')
            ->select(array('leaveid'))
            ->where('appstatus', $appstatus)
            ->where('view_status', $view_status)
            ->where('fdate', '>=', $fdate)->count();
    }

    public static function EmployeeNotificationstatus($user_id, $role, $cid)
    {
        $notification_status = array();
        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $pending = 1;
        $approved = 2;
        $disaproved = 3;

        if ($role == 1) {
//            DB::enableQueryLog();
            $notification_status['leave_pending'] = Leave::where('empid', $user_id)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->whereIn('appstatus', array(1, 4))
//                ->orwhere('appstatus',4)
                ->count();

//                    $query = DB::getQueryLog();
            //        print_r($query);
            //        die;
            //            $notification_status['Leave_pending_sum']=Leave::where('empid',$user_id)
            //                ->where('appstatus',1)
            //                ->where('clientid',$cid)
            //                ->where('fdate','>=',$min_date)
            //                ->where('tdate','<=',$max_date)
            //                ->sum('nod');

            $pending_leave = DB::table('proll_leave')
                ->where('proll_leave.empid', '=', $user_id)
                ->where('proll_leave.appstatus', '=', 1)
                ->where('proll_leave.fdate', '>=', $min_date)
                ->where('proll_leave.tdate', '<=', $max_date)
                ->sum('proll_leave.nod');

            $resubmit_leave = DB::table('proll_leave')
                ->where('proll_leave.empid', '=', $user_id)
                ->where('proll_leave.appstatus', '=', 4)
                ->where('proll_leave.fdate', '>=', $min_date)
                ->where('proll_leave.tdate', '<=', $max_date)
                ->sum('proll_leave.nod');

            $notification_status['leave_pending_sum'] = $pending_leave + $resubmit_leave;
//            $notification_status['leave_pending']= $pending_leave + $resubmit_leave;

            $notification_status['leave_approved'] = Leave::where('empid', $user_id)
                ->where('appstatus', 2)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->count();
            $notification_status['leave_approved_sum'] = Leave::where('empid', $user_id)
                ->where('appstatus', 2)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->sum('nod');

            $notification_status['leave_disapproved'] = Leave::where('empid', $user_id)
                ->where('appstatus', 3)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->count();

            $notification_status['leave_disapproved_sum'] = Leave::where('empid', $user_id)
                ->where('appstatus', 3)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->sum('nod');

            $gender = DB::table('proll_employee_detail')
                ->where('empid', $user_id)
                ->value('gender');
            $doj = DB::table('proll_employee')
                ->where('id', $user_id)
                ->value('doj');

            $current_date = Carbon::now()->toDateString();
            $diff = abs(strtotime($current_date) - strtotime($doj));
            $years = floor($diff / (365 * 60 * 60 * 24));

            if ($gender == 'Male') {
                /*if ($years >= 1) {*/
                $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
                    ->where('cid', $cid)
                    ->where('is_quota_show', 1)
                    ->where('type', '!=', 'maternity')
                    ->sum('permanent_staff_quota');
                /*} else {
            $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
            ->where('cid', $cid)
            ->where('is_quota_show', 1)
            ->where('type', '!=', 'maternity')
            ->where('type', '!=', 'annual')
            ->sum('permanent_staff_quota');
            }*/
            } else {
                /*if ($years >= 1) {*/
                $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
                    ->where('cid', $cid)
                    ->where('is_quota_show', 1)
                    ->sum('permanent_staff_quota');
                /*} else {
            $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
            ->where('cid', $cid)
            ->where('is_quota_show', 1)
            ->where('type', '!=', 'annual')
            ->sum('permanent_staff_quota');
            }*/
            }

            $notification_status['leave_balance'] = floatval($notification_status['leave_allocate'] - $notification_status['leave_approved_sum'] - $notification_status['leave_pending_sum']);

//            $notification_status['expense_pending']=Expense::where('eid',$user_id)
            //                ->where('lm_status',2)
            //                ->where('hr_status',1)
            //                ->where('cid',$cid)
            //                ->where('added_on','>=',$min_date)
            //                ->where('added_on','<=',$max_date)
            //                ->count();
            //        dd($notification_status);
            $notification_status['expense_pending'] = Employee::pending_expense_count_by_status($user_id, $pending, $min_date, $max_date, $cid);
            $notification_status['expense_approved'] = Employee::expense_recived_status_employee($user_id, $approved, $min_date, $max_date, $cid);
            $notification_status['expense_disapproved'] = Employee::disapproved_expense_count_by_status($user_id, $disaproved, $min_date, $max_date, $cid);
            $notification_status['travel_approved'] = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 2)
                ->where('admin_status', 2)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending1 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 1)
                ->where('hr_status', 1)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending2 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 1)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending3 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 2)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $notification_status['travel_pending'] = $travel_pending1 + $travel_pending2 + $travel_pending3;
            $travel_disapproved1 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 1)
                ->where('lm_status', 3)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_disapproved2 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 3)
                ->where('lm_status', 2)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_disapproved3 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 2)
                ->where('lm_status', 2)
                ->where('admin_status', 3)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();

            $notification_status['travel_disapproved'] = $travel_disapproved1 + $travel_disapproved2 + $travel_disapproved3;
            $notification_status['appraisal_approved'] = 0;
            $notification_status['appraisal_pending'] = 0;
            $notification_status['appraisal_disapproved'] = 0;
            $notification_status['employee_exit_management_approved'] = 0;
            $notification_status['employee_exit_management_pending'] = 0;
            $notification_status['employee_exit_management_disapproved'] = 0;
            $notification_status['others_approved'] = 0;
            $notification_status['others_pending'] = 0;
            $notification_status['others_disapproved'] = 0;

            $notification_status['leave_total'] = $notification_status['leave_pending'] + $notification_status['leave_approved'] + $notification_status['leave_disapproved'];
            $notification_status['expense_total'] = $notification_status['expense_pending'] + $notification_status['expense_approved'] + $notification_status['expense_disapproved'];
            $notification_status['travel_total'] = $notification_status['travel_approved'] + $notification_status['travel_pending'] + $notification_status['travel_disapproved'];
            $notification_status['appraisal_total'] = 0;
            $notification_status['employee_exit_management_approved_total'] = 0;
            $notification_status['others_total'] = 0;
            $notification_status['leave_pending'] = $pending_leave + $resubmit_leave;
            return $notification_status;
        } elseif ($role == 2) {

            $users = Employee::ReportingUserInfo($user_id);

            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $pending = 1;
            $approved = 2;
            $disaproved = 3;
            $resubmit = 4;
            $users_ids = array();
            foreach ($users as $user) {
                $users_ids[] = $user->id;
            }

            $status_list_leave_pending = Employee::leave_recived($users_ids, $pending, $min_date, $max_date, $cid);
            $status_list_leave_resubmit = Employee::leave_recived($users_ids, $resubmit, $min_date, $max_date, $cid);
            $status_list['leave_pending'] = $status_list_leave_pending;
            $status_list['expense_pending'] = Employee::expense_recived_status_lm($users_ids, $pending, $min_date, $max_date, $cid);
            $status_list['travel_pending'] = Employee::travel_recived($users_ids, $pending, $min_date, $max_date, $cid);
            $status_list['leave_approved'] = Employee::leave_recived($users_ids, $approved, $min_date, $max_date, $cid);
            $status_list['expense_approved'] = Employee::expense_recived_status_lm($users_ids, $approved, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived($users_ids, $approved, $min_date, $max_date, $cid);
            $status_list['leave_disaproved'] = Employee::leave_recived($users_ids, $disaproved, $min_date, $max_date, $cid);
            $status_list['expense_disaproved'] = Employee::expense_recived_status_lm($users_ids, $disaproved, $min_date, $max_date, $cid);
            $status_list['travel_disaproved'] = Employee::travel_recived($users_ids, $disaproved, $min_date, $max_date, $cid);
            $status_list['leave_total'] = $status_list['leave_pending'] + $status_list['leave_approved'] + $status_list['leave_disaproved'];
            $status_list['expense_total'] = $status_list['expense_pending'] + $status_list['expense_approved'] + $status_list['expense_disaproved'];
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disaproved'];
            $status_list['appraisal_approved'] = 0;
            $status_list['appraisal_pending'] = 0;
            $status_list['appraisal_disapproved'] = 0;
            $status_list['employee_exit_management_approved'] = 0;
            $status_list['employee_exit_management_pending'] = 0;
            $status_list['employee_exit_management_disapproved'] = 0;

            return $status_list;
        } elseif ($role == 3) {

            $users_ids = Employee::GetUserListByHR($cid);
            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $pending = 1;
            $approved = 2;
            $disaproved = 3;
            $resubmit = 4;
            $status_list['leave_pending'] = Employee::leave_recived($users_ids, $pending, $min_date, $max_date, $cid);
//            $status_list_leave_resubmit=Employee::leave_recived($users_ids,$resubmit,$min_date,$max_date,$cid);
            //            $status_list['leave_pending']=$status_list_leave_pending+$status_list_leave_resubmit;
            $status_list['expense_pending'] = Employee::expense_recived_status_hr($users_ids, $approved, $pending, $min_date, $max_date, $cid);
            $status_list['travel_pending'] = Employee::travel_recived_status_hr($users_ids, $approved, $pending, $min_date, $max_date, $cid);

            $status_list['leave_approved'] = Employee::leave_recived($users_ids, $approved, $min_date, $max_date, $cid);
            $status_list['expense_approved'] = Employee::expense_recived_status_hr($users_ids, $approved, $approved, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived_status_hr($users_ids, $approved, $approved, $min_date, $max_date, $cid);

            $status_list['leave_disaproved'] = Employee::leave_recived($users_ids, $disaproved, $min_date, $max_date, $cid);
            $status_list['expense_disaproved'] = Employee::expense_recived_status_hr($users_ids, $approved, $disaproved, $min_date, $max_date, $cid);
            $status_list['travel_disaproved'] = Employee::travel_recived_status_hr($users_ids, $approved, $disaproved, $min_date, $max_date, $cid);

            $status_list['leave_total'] = $status_list['leave_pending'] + $status_list['leave_approved'] + $status_list['leave_disaproved'];
            $status_list['expense_total'] = $status_list['expense_pending'] + $status_list['expense_approved'] + $status_list['expense_disaproved'];
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disaproved'];

            $status_list['appraisal_approved'] = 0;
            $status_list['appraisal_pending'] = 0;
            $status_list['appraisal_disapproved'] = 0;
            $status_list['employee_exit_management_approved'] = 0;
            $status_list['employee_exit_management_pending'] = 0;
            $status_list['employee_exit_management_disapproved'] = 0;

            return $status_list;
        } elseif ($role == 4) {

            $users_ids = Employee::GetUserListByHR($cid);
            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $pending = 1;
            $approved = 2;
            $disaproved = 3;
            $resubmit = 4;
            $status_list["leave_pending"] = 0;
            $status_list["expense_pending"] = 0;
            $status_list["leave_approved"] = 0;
            $status_list["expense_approved"] = 0;
            $status_list["leave_disaproved"] = 0;
            $status_list["expense_disaproved"] = 0;
            $status_list["leave_total"] = 0;
            $status_list["expense_total"] = 0;
            $status_list["appraisal_approved"] = 0;
            $status_list["appraisal_pending"] = 0;
            $status_list["appraisal_disapproved"] = 0;
            $status_list["employee_exit_management_approved"] = 0;
            $status_list["employee_exit_management_pending"] = 0;
            $status_list["employee_exit_management_disapproved"] = 0;
            $status_list['travel_pending'] = Employee::travel_recived_status_admin($users_ids, $approved, $pending, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived_status_admin($users_ids, $approved, $approved, $min_date, $max_date, $cid);
            $status_list['travel_disaproved'] = Employee::travel_recived_status_admin($users_ids, $approved, $disaproved, $min_date, $max_date, $cid);
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disaproved'];

            return $status_list;
        }
    }

    public static function EmployeeNotificationstatus_v2_3($user_id, $role, $cid)
    {
        $notification_status = array();
        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $pending = 1;
        $approved = 2;
        $disaproved = 3;

        if ($role == 1) {
//            DB::enableQueryLog();
            $notification_status['leave_pending'] = Leave::where('empid', $user_id)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->where('appstatus', '!=', 3)
                ->where('hr_status', '!=', 3)
                ->where(function ($query) {
                    $query->whereIn('appstatus', [1, 4]);
                    $query->orWhereIn('hr_status', [1, 4]);
                })
                ->count();

            $pending_leave = DB::table('proll_leave')
                ->where('proll_leave.empid', '=', $user_id)
                ->where('appstatus', '!=', 3)
                ->where(function ($query) {
                    $query->where('appstatus', 1);
                    $query->where('hr_status', 1);
                })
                ->where('proll_leave.fdate', '>=', $min_date)
                ->where('proll_leave.tdate', '<=', $max_date)
                ->sum('proll_leave.nod');

            $resubmit_leave = DB::table('proll_leave')
                ->where('proll_leave.empid', '=', $user_id)
                ->where(function ($query) {
                    $query->where('appstatus', 4);
                    $query->where('hr_status', 4);
                })
                ->where('proll_leave.fdate', '>=', $min_date)
                ->where('proll_leave.tdate', '<=', $max_date)
                ->sum('proll_leave.nod');

            $notification_status['leave_pending_sum'] = $pending_leave + $resubmit_leave;

            $notification_status['leave_approved'] = Leave::where('empid', $user_id)
                ->where('appstatus', 2)
                ->where('hr_status', 2)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->count();
            $notification_status['leave_approved_sum'] = Leave::where('empid', $user_id)
                ->where('appstatus', 2)
                ->where('hr_status', 2)
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->sum('nod');

            $notification_status['leave_disapproved'] = Leave::where('empid', $user_id)
                ->where(function ($query) {
                    $query->where('appstatus', 3);
                    $query->orWhere('hr_status', 3);
                })
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->count();

            $notification_status['leave_disapproved_sum'] = Leave::where('empid', $user_id)
                ->where(function ($query) {
                    $query->where('appstatus', 3);
                    $query->orWhere('hr_status', 3);
                })
                ->where('clientid', $cid)
                ->where('fdate', '>=', $min_date)
                ->where('tdate', '<=', $max_date)
                ->sum('nod');

            $gender = DB::table('proll_employee_detail')
                ->where('empid', $user_id)
                ->value('gender');
            $doj = DB::table('proll_employee')
                ->where('id', $user_id)
                ->value('doj');

            $current_date = Carbon::now()->toDateString();
            $diff = abs(strtotime($current_date) - strtotime($doj));
            $years = floor($diff / (365 * 60 * 60 * 24));

            if ($gender == 'Male') {
                /*if ($years >= 1) {*/
                $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
                    ->where('cid', $cid)
                    ->where('is_quota_show', 1)
                    ->where('type', '!=', 'maternity')
                    ->sum('permanent_staff_quota');
                /*} else {
            $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
            ->where('cid', $cid)
            ->where('is_quota_show', 1)
            ->where('type', '!=', 'maternity')
            ->where('type', '!=', 'annual')
            ->sum('permanent_staff_quota');
            }*/
            } else {
                /*if ($years >= 1) {*/
                $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
                    ->where('cid', $cid)
                    ->where('is_quota_show', 1)
                    ->sum('permanent_staff_quota');
                /*} else {
            $notification_status['leave_allocate'] = DB::table('proll_leave_type_c')
            ->where('cid', $cid)
            ->where('is_quota_show', 1)
            ->where('type', '!=', 'annual')
            ->sum('permanent_staff_quota');
            }*/
            }

            $notification_status['leave_balance'] = floatval($notification_status['leave_allocate'] - $notification_status['leave_approved_sum'] - $notification_status['leave_pending_sum']);

            $notification_status['expense_pending'] = Employee::pending_expense_count_by_status($user_id, $pending, $min_date, $max_date, $cid);
            $notification_status['expense_approved'] = Employee::expense_recived_status_employee($user_id, $approved, $min_date, $max_date, $cid);
            $notification_status['expense_disapproved'] = Employee::disapproved_expense_count_by_status($user_id, $disaproved, $min_date, $max_date, $cid);
            $notification_status['travel_approved'] = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 2)
                ->where('admin_status', 2)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending1 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 1)
                ->where('hr_status', 1)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending2 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 1)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_pending3 = Travel::where('emp_id', $user_id)
                ->where('lm_status', 2)
                ->where('hr_status', 2)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $notification_status['travel_pending'] = $travel_pending1 + $travel_pending2 + $travel_pending3;
            $travel_disapproved1 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 1)
                ->where('lm_status', 3)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_disapproved2 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 3)
                ->where('lm_status', 2)
                ->where('admin_status', 1)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();
            $travel_disapproved3 = Travel::where('emp_id', $user_id)
                ->where('hr_status', 2)
                ->where('lm_status', 2)
                ->where('admin_status', 3)
                ->where('client_id', $cid)
                ->where('added', '>=', $min_date)
                ->where('added', '<=', $max_date)
                ->count();

            $notification_status['travel_disapproved'] = $travel_disapproved1 + $travel_disapproved2 + $travel_disapproved3;
            $notification_status['appraisal_approved'] = 0;
            $notification_status['appraisal_pending'] = 0;
            $notification_status['appraisal_disapproved'] = 0;
            $notification_status['employee_exit_management_approved'] = 0;
            $notification_status['employee_exit_management_pending'] = 0;
            $notification_status['employee_exit_management_disapproved'] = 0;
            $notification_status['others_approved'] = 0;
            $notification_status['others_pending'] = 0;
            $notification_status['others_disapproved'] = 0;

            $notification_status['leave_total'] = $notification_status['leave_pending'] + $notification_status['leave_approved'] + $notification_status['leave_disapproved'];
            $notification_status['expense_total'] = $notification_status['expense_pending'] + $notification_status['expense_approved'] + $notification_status['expense_disapproved'];
            $notification_status['travel_total'] = $notification_status['travel_approved'] + $notification_status['travel_pending'] + $notification_status['travel_disapproved'];
            $notification_status['appraisal_total'] = 0;
            $notification_status['employee_exit_management_approved_total'] = 0;
            $notification_status['others_total'] = 0;
            $notification_status['leave_pending'] = $pending_leave + $resubmit_leave;
            return $notification_status;
        } elseif ($role == 2) {

            $module = 'leave';
            $role = 'LM';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, $module, $role, $user_id);
            $status_list['leave_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['leave_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['leave_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $module = 'expense';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, 'expense', 'LM', $user_id);
            $status_list['expense_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['expense_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['expense_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $module = 'travel';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, $module, $role, $user_id);
            $status_list['travel_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['travel_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['travel_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $status_list['leave_total'] = $status_list['leave_pending'] + $status_list['leave_approved'] + $status_list['leave_disapproved'];
            $status_list['expense_total'] = $status_list['expense_pending'] + $status_list['expense_approved'] + $status_list['expense_disapproved'];
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disapproved'];

            $status_list['appraisal_approved'] = 0;
            $status_list['appraisal_pending'] = 0;
            $status_list['appraisal_disapproved'] = 0;
            $status_list['employee_exit_management_approved'] = 0;
            $status_list['employee_exit_management_pending'] = 0;
            $status_list['employee_exit_management_disapproved'] = 0;

            return $status_list;
        } elseif ($role == 3) {

            $module = 'leave';
            $role = 'HR';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, $module, $role, $user_id);
            $status_list['leave_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['leave_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['leave_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $module = 'expense';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, 'expense', 'LM', $user_id);
            $status_list['expense_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['expense_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['expense_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $module = 'travel';
            $my_queue_apps = MultiApprovalHelpers::get_in_my_queue_applications($cid, $module, $role, $user_id);
            $status_list['travel_pending'] = ($my_queue_apps ? count(explode(',', $my_queue_apps)) : 0);
            $status_list['travel_approved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 2);
            $status_list['travel_disapproved'] = MultiApprovalHelpers::get_app_count_by_status($cid, $module, $role, $user_id, $status = 3);

            $status_list['leave_total'] = $status_list['leave_pending'] + $status_list['leave_approved'] + $status_list['leave_disapproved'];
            $status_list['expense_total'] = $status_list['expense_pending'] + $status_list['expense_approved'] + $status_list['expense_disapproved'];
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disapproved'];

            $status_list['appraisal_approved'] = 0;
            $status_list['appraisal_pending'] = 0;
            $status_list['appraisal_disapproved'] = 0;
            $status_list['employee_exit_management_approved'] = 0;
            $status_list['employee_exit_management_pending'] = 0;
            $status_list['employee_exit_management_disapproved'] = 0;

            return $status_list;
        } elseif ($role == 4) {

            $users_ids = Employee::GetUserListByHR($cid);
            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $pending = 1;
            $approved = 2;
            $disaproved = 3;
            $resubmit = 4;
            $status_list["leave_pending"] = 0;
            $status_list["expense_pending"] = 0;
            $status_list["leave_approved"] = 0;
            $status_list["expense_approved"] = 0;
            $status_list["leave_disapproved"] = 0;
            $status_list["expense_disapproved"] = 0;
            $status_list["leave_total"] = 0;
            $status_list["expense_total"] = 0;
            $status_list["appraisal_approved"] = 0;
            $status_list["appraisal_pending"] = 0;
            $status_list["appraisal_disapproved"] = 0;
            $status_list["employee_exit_management_approved"] = 0;
            $status_list["employee_exit_management_pending"] = 0;
            $status_list["employee_exit_management_disapproved"] = 0;
            $status_list['travel_pending'] = Employee::travel_recived_status_admin($users_ids, $approved, $pending, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived_status_admin($users_ids, $approved, $approved, $min_date, $max_date, $cid);
            $status_list['travel_disapproved'] = Employee::travel_recived_status_admin($users_ids, $approved, $disaproved, $min_date, $max_date, $cid);
            $status_list['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disapproved'];

            return $status_list;
        }
    }

    public static function EmployeeNotificationList($user_id, $role, $cid)
    {
        $notification_status = array();
        $notification_total = array();
        $notification_list = array();
        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $lm_pending = 1;
        $lm_approved = 2;
        $lm_disapproved = 3;
        $lm_resubmit = 4;

        $view_status = 0;
        $hr_pending = 1;
        $hr_approved = 2;
        $hr_disaproved = 3;
        $hr_resubmit = 4;

        $admin_approved = 2;
        $admin_disaproved = 3;
        $admin_resubmit = 4;

//        var_dump($user_id,$role,$cid);die;

        if ($role == 1) {
            /*             * ***********Employee Leave Updates List************* */
            $notification_status['leave_pending'] = Employee::leave_view_recived($user_id, $lm_pending, $view_status, $min_date, $max_date, $cid);
            $notification_status['leave_approved'] = Employee::leave_view_recived($user_id, $lm_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['leave_disapproved'] = Employee::leave_view_recived($user_id, $lm_disapproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['leave_resubmit'] = Employee::leave_view_recived($user_id, $lm_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * ***********Employee Expense Updates  List  LM ************* */

            $notification_status['lm_expense_pending'] = Employee::Expense_view_recived($user_id, $lm_pending, $view_status, $min_date, $max_date, $cid);
            $notification_status['lm_expense_approved'] = Employee::Expense_view_recived($user_id, $lm_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['lm_expense_disapproved'] = Employee::Expense_view_recived($user_id, $lm_disapproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['lm_expense_resubmit'] = Employee::Expense_view_recived($user_id, $lm_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * ***********Employee Expense Updates  List  HR ************* */
            $notification_status['hr_expense_approved'] = Employee::Expense_view_recived_HR($user_id, $lm_approved, $hr_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['hr_expense_disapproved'] = Employee::Expense_view_recived_HR($user_id, $lm_approved, $hr_disaproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['hr_expense_resubmit'] = Employee::Expense_view_recived_HR($user_id, $lm_approved, $hr_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * *******************Get all travel appilcation count*************************************** */
//            $notification_status['lm_travel_approved']= Employee::Travel_view_recived_LM($user_id,$lm_approved,$view_status,$min_date,$max_date,$cid);

            /*             * ***********Employee Travel Updates  List  LM ************* */
            $notification_status['lm_travel_approved'] = Employee::Travel_view_recived_LM($user_id, $lm_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['lm_travel_disapproved'] = Employee::Travel_view_recived_LM($user_id, $lm_disapproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['lm_travel_resubmit'] = Employee::Travel_view_recived_LM($user_id, $lm_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * ***********Employee Travel Updates  List  HR ************* */
            $notification_status['hr_travel_approved'] = Employee::Travel_view_recived_HR($user_id, $lm_approved, $hr_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['hr_travel_disapproved'] = Employee::Travel_view_recived_HR($user_id, $lm_approved, $hr_disaproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['hr_travel_resubmit'] = Employee::Travel_view_recived_HR($user_id, $lm_approved, $hr_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * ***********Employee Travel Updates  List  Admin ************* */

            $notification_status['admin_travel_approved'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['admin_travel_disapproved'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_disaproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['admin_travel_resubmit'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_resubmit, $view_status, $min_date, $max_date, $cid);

            /*             * **********************Leave Status By LM************************ */

            if ($notification_status['leave_pending'] > 0) {
                $notification_list['leave_pending'] = $notification_status['leave_pending'] . '  Leave Applications have been Pending';
            }

            if ($notification_status['leave_approved'] > 0) {
                $notification_list['leave_approved'] = $notification_status['leave_approved'] . ' Leave Applications have been approved';
            }

            if ($notification_status['leave_disapproved'] > 0) {
                $notification_list['leave_disapproved'] = $notification_status['leave_disapproved'] . ' Leave Applications have been disapproved';
            }

            if ($notification_status['leave_resubmit'] > 0) {
                $notification_list['leave_resubmit'] = $notification_status['leave_resubmit'] . ' Leave Applications have been resubmit';
            }

            $notification_total['leave_total'] = $notification_status['leave_pending'] + $notification_status['leave_approved'] + $notification_status['leave_disapproved'] + $notification_status['leave_resubmit'];

            /*             * **********************Expense Status By LM************************ */

            if ($notification_status['lm_expense_approved'] > 0) {
                $notification_list['lm_expense_approved'] = $notification_status['lm_expense_approved'] . ' Expense Applications have been approved by LM';
            }

            if ($notification_status['lm_expense_pending'] > 0) {
                $notification_list['lm_expense_pending'] = $notification_status['lm_expense_pending'] . ' Expense Applications have been pending by LM';
            }

            if ($notification_status['lm_expense_disapproved'] > 0) {
                $notification_list['lm_expense_disapproved'] = $notification_status['lm_expense_disapproved'] . ' Expense Applications have been disapproved by LM';
            }

            if ($notification_status['lm_expense_resubmit'] > 0) {
                $notification_list['lm_expense_resubmit'] = $notification_status['lm_expense_resubmit'] . ' Expense Applications have been resubmit by LM';
            }

            /*             * **********************Expense Status By HR************************ */

            if ($notification_status['hr_expense_approved'] > 0) {
                $notification_list['hr_expense_approved'] = $notification_status['hr_expense_approved'] . ' Expense Applications have been approved by HR';
            }

            if ($notification_status['hr_expense_disapproved'] > 0) {
                $notification_list['hr_expense_disapproved'] = $notification_status['hr_expense_disapproved'] . ' Expense Applications have been disapproved by HR';
            }

            if ($notification_status['hr_expense_resubmit'] > 0) {
                $notification_list['hr_expense_resubmit'] = $notification_status['hr_expense_resubmit'] . ' Expense Applications have been resubmit by HR';
            }

            $notification_total['expense_total'] = $notification_status['lm_expense_approved'] + $notification_status['lm_expense_pending'] + $notification_status['lm_expense_disapproved'] + $notification_status['lm_expense_resubmit'] + $notification_status['hr_expense_approved'] + $notification_status['hr_expense_disapproved'] + $notification_status['hr_expense_resubmit'];

            /*             * **********************Travel Status By LM************************ */

            if ($notification_status['lm_travel_approved'] > 0) {
                $notification_list['lm_travel_approved'] = $notification_status['lm_travel_approved'] . ' Travel Applications have been approved by LM';
            }

            if ($notification_status['lm_travel_disapproved'] > 0) {
                $notification_list['lm_travel_disapproved'] = $notification_status['lm_travel_disapproved'] . ' Travel Applications have been disapproved by LM';
            }

            if ($notification_status['lm_travel_resubmit'] > 0) {
                $notification_list['lm_travel_resubmit'] = $notification_status['lm_travel_resubmit'] . ' Travel Applications have been resubmit by LM';
            }

            /*             * **********************Travel Status By HR************************ */

            if ($notification_status['hr_travel_approved'] > 0) {
                $notification_list['hr_travel_approved'] = $notification_status['hr_travel_approved'] . ' Travel Applications have been approved by HR';
            }

            if ($notification_status['hr_travel_disapproved'] > 0) {
                $notification_list['hr_travel_disapproved'] = $notification_status['hr_travel_disapproved'] . ' Travel Applications have been disapproved by HR';
            }

            if ($notification_status['hr_travel_resubmit'] > 0) {
                $notification_list['hr_travel_resubmit'] = $notification_status['hr_travel_resubmit'] . ' Travel Applications have been resubmit by HR';
            }

            /*             * **********************Travel Status By Admin************************ */

            if ($notification_status['admin_travel_approved'] > 0) {
                $notification_list['admin_travel_approved'] = $notification_status['admin_travel_approved'] . ' Travel Applications have been approved by Admin';
            }

            if ($notification_status['admin_travel_disapproved'] > 0) {
                $notification_list['admin_travel_disapproved'] = $notification_status['admin_travel_disapproved'] . ' Travel Applications have been disapproved by Admin';
            }

            if ($notification_status['admin_travel_resubmit'] > 0) {
                $notification_list['admin_travel_resubmit'] = $notification_status['admin_travel_resubmit'] . ' Travel Applications have been resubmit by Admin';
            }

//            $notification_total['travel_total'] = Employee::Get_all_travel_views_count($user_id, $lm_approved, $view_status, $min_date, $max_date, $cid);

            $notification_total['travel_total'] = Employee::getEmployeeTravelNotificationCount($user_id, $cid, $min_date, $max_date);

        } elseif ($role == 2) {

            $status_list = array();
            $status_list_message = array();
            $pending_status = 1;
            $approved_status = 2;
            $disaproved_status = 3;
            $lm_view_status = 0;
            $users_ids = array();
            $users = Employee::ReportingUserInfo($user_id);
            foreach ($users as $user) {
                $users_ids[] = $user->id;
            }

            $status_list['leave_pending'] = Employee::leave_recived_LM_view($users_ids, $pending_status, $lm_view_status, $min_date, $max_date, $cid);
            $status_list['expense_pending'] = Employee::expense_recived_LM_view($users_ids, $pending_status, $lm_view_status, $min_date, $max_date, $cid);
            $status_list['travel_pending'] = Employee::travel_recived_LM_view($users_ids, $pending_status, $lm_view_status, $min_date, $max_date, $cid);
            $status_list['expense_disapproved'] = Employee::expense_recived_HR_view($users_ids, $approved_status, $disaproved_status, $lm_view_status, $min_date, $max_date, $cid);
            $status_list['expense_approved'] = Employee::expense_recived_HR_view($users_ids, $approved_status, $approved_status, $lm_view_status, $min_date, $max_date, $cid);
            $status_list['travel_disapproved'] = Employee::travel_recived_HR_view($users_ids, $approved_status, $disaproved_status, $lm_view_status, $min_date, $max_date, $cid);

            if ($status_list['leave_pending'] > 0) {
                $status_list_message['leave_pending'] = $status_list['leave_pending'] . ' Leave Applications have been received.';
            }
            if ($status_list['expense_pending'] > 0) {
                $status_list_message['expense_pending'] = $status_list['expense_pending'] . ' Expense Applications have been received.';
            }

            if ($status_list['travel_pending'] > 0) {
                $status_list_message['travel_pending'] = $status_list['travel_pending'] . ' Travel Applications have been received.';
            }

            if ($status_list['expense_disapproved'] > 0) {
                $status_list_message['expense_disapproved'] = $status_list['expense_disapproved'] . ' Expense Applications have been disapproved';
            }
            if ($status_list['expense_approved'] > 0) {
                $status_list_message['expense_approved'] = $status_list['expense_approved'] . ' Expense Applications have been approved.';
            }
            if ($status_list['travel_disapproved'] > 0) {
                $status_list_message['travel_disapproved'] = $status_list['travel_disapproved'] . ' Travel Applications have been disapproved.';
            }

            $notification_total['leave_total'] = $status_list['leave_pending'];
            $notification_total['expense_total'] = $status_list['expense_pending'] + $status_list['expense_disapproved'] + $status_list['expense_approved'];
//            $notification_total['travel_total'] = $status_list['travel_pending'] + $status_list['travel_disapproved'];
            $notification_total['travel_total'] = Employee::getLMTravelNotificationCount($users_ids, $cid, $min_date, $max_date);

//        return $status_list_message;
        } elseif ($role == 3) {

            $users_ids = Employee::GetUserListByHR($cid);

            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $status_list_message = array();
            $pending_status = 1;
            $approved_status = 2;
            $disaproved_status = 3;

            $status_list['leave_approved'] = Employee::leave_recive_HR_HR_view($users_ids, $approved_status, $view_status, $min_date, $max_date, $cid);
            $status_list['leave_disapproved'] = Employee::leave_recive_HR_HR_view($users_ids, $disaproved_status, $view_status, $min_date, $max_date, $cid);

            $notification_total['expense_total'] = Employee::expense_recived_HR_HR_view($users_ids, $lm_approved, $pending_status, $view_status, $min_date, $max_date, $cid);
//            $notification_total['expense_total']=$status_list['expense_pending']+$status_list['expense_disapproved'];
            //            $status_list['expense_disapproved']=Employee::expense_recived_HR_HR_view($users_ids,$approved_status,$disaproved_status,$view_status,$min_date,$max_date,$cid);

            $status_list['travel_pending'] = Employee::travel_recived_HR_HR_view($users_ids, $approved_status, $pending_status, $view_status, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived_HR_admin_view($users_ids, $approved_status, $approved_status, $approved_status, $view_status, $min_date, $max_date, $cid);
            $status_list['travel_disapproved'] = Employee::travel_recived_HR_admin_view($users_ids, $approved_status, $approved_status, $disaproved_status, $view_status, $min_date, $max_date, $cid);

            if ($status_list['leave_approved'] > 0) {
                $status_list_message['leave_approved'] = $status_list['leave_approved'] . ' Leave Applications have been approved.';
            }
            if ($status_list['leave_disapproved'] > 0) {
                $status_list_message['leave_disapproved'] = $status_list['leave_disapproved'] . ' Leave Applications have been disapproved.';
            }

            $notification_total['leave_total'] = $status_list['leave_approved'] + $status_list['leave_approved'];
//            if($status_list['expense_pending']>0){
            //                $status_list_message['expense_pending']=$status_list['expense_pending'] .' Expense Applications have been received.' ;
            //            }
            //            if($status_list['expense_disapproved']>0){
            //                $status_list_message['expense_disapproved']=$status_list['expense_disapproved'] .' Expense Applications have been disapproved.' ;
            //            }
            //            $notification_total['expense_total']=$status_list['expense_pending']+$status_list['expense_disapproved'];

            if ($status_list['travel_pending'] > 0) {
                $status_list_message['travel_pending'] = $status_list['travel_pending'] . ' Travel Applications have been received.';
            }
            if ($status_list['travel_approved'] > 0) {
                $status_list_message['travel_approved'] = $status_list['travel_approved'] . ' Travel Applications have been approved.';
            }
            if ($status_list['travel_disapproved'] > 0) {
                $status_list_message['travel_disapproved'] = $status_list['travel_disapproved'] . ' Travel Applications have been disapproved.';
            }

//            $notification_total['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disapproved'];
            $notification_total['travel_total'] = Employee::getHRTravelNotificationCount($users_ids, $cid, $min_date, $max_date);
//        return $status_list_message;
        } else if ($role == 4) {
            $users_ids = Employee::GetUserListByHR($cid);

            $dates = User::getFiscalYearDatesByModule('Leave Add');
            $min_date = $dates->mini;
            $max_date = $dates->maxi;
            $status_list = array();
            $status_list_message = array();
            $pending_status = 1;
            $approved_status = 2;
            $disaproved_status = 3;

            $status_list['travel_pending'] = Employee::travel_recived_Admin_view($users_ids, $approved_status, $pending_status, $view_status, $min_date, $max_date, $cid);
            $status_list['travel_approved'] = Employee::travel_recived_Admin_view($users_ids, $approved_status, $approved_status, $view_status, $min_date, $max_date, $cid);
            $status_list['travel_disapproved'] = Employee::travel_recived_Admin_view($users_ids, $approved_status, $disaproved_status, $view_status, $min_date, $max_date, $cid);

//            $status_list['travel_approved']=Employee::travel_recived_HR_admin_view($users_ids,$approved_status,$approved_status,$approved_status,$view_status,$min_date,$max_date,$cid);
            //            $status_list['travel_disapproved']=Employee::travel_recived_HR_admin_view($users_ids,$approved_status,$approved_status,$disaproved_status,$view_status,$min_date,$max_date,$cid);

            if ($status_list['travel_pending'] > 0) {
                $status_list_message['travel_pending'] = $status_list['travel_pending'] . ' Travel Applications have been received.';
            }
            if ($status_list['travel_approved'] > 0) {
                $status_list_message['travel_approved'] = $status_list['travel_approved'] . ' Travel Applications have been approved.';
            }
            if ($status_list['travel_disapproved'] > 0) {
                $status_list_message['travel_disapproved'] = $status_list['travel_disapproved'] . ' Travel Applications have been disapproved.';
            }

            $notification_total['travel_total'] = $status_list['travel_pending'] + $status_list['travel_approved'] + $status_list['travel_disapproved'];
            $notification_total['travel_total'] = Employee::getAdminTravelNotificationCount($users_ids, $cid, $min_date, $max_date);
        } else {
            return 0;
        }

//        $notification_total['attendance_total']=0;
        //        $notification_total['loan_total']=0;
        //        $notification_total['apprisal_total']=0;
        return $notification_total;
    }

    public static function EmployeeNotificationList_v2_3($user_id, $role, $cid)
    {
        $notification_status = array();
        $notification_total = array();
        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        $lm_approved = 2;

        $view_status = 0;
        $hr_approved = 2;

        $admin_approved = 2;
        $admin_disaproved = 3;
        $admin_resubmit = 4;

        if ($role == 1) {
            /*             * ***********Employee Leave Updates List************* */
            $module = "leave";
            $module_tbl = "proll_leave";
            $module_pkey = "leaveid";
            $where = " AND $module_tbl.empid=$user_id AND $module_tbl.fdate >='$min_date' AND $module_tbl.tdate <='$max_date'";

            $notification_status['leave_approved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 2, $where));
            $notification_status['leave_disapproved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 3, $where));
            $notification_status['leave_resubmit'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 4, $where));

            /*             * ***********Employee Expense Updates  ************* */
            $module = "expense";
            $module_tbl = "proll_expense";
            $module_pkey = "id";
            $where = " AND $module_tbl.eid=$user_id AND $module_tbl.added_on >='$min_date' AND $module_tbl.added_on <='$max_date'";
            $notification_status['expense_approved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 2, $where));
            $notification_status['expense_disapproved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 3, $where));
            $notification_status['expense_resubmit'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 4, $where));

            /*             * ***********Employee Travel Updates  List ************* */
            $module = "travel";
            $module_tbl = "proll_travel";
            $module_pkey = "id";
            $where = " AND $module_tbl.emp_id=$user_id AND $module_tbl.added >='$min_date' AND $module_tbl.added <='$max_date'";
            $notification_status['travel_approved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 2, $where));
            $notification_status['travel_disapproved'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 3, $where));
            $notification_status['travel_resubmit'] = count(MultiApprovalHelpers::get_applications_by_status($cid, $module, $module_tbl, $module_pkey, $approval_status = 4, $where));

            $notification_status['admin_travel_approved'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_approved, $view_status, $min_date, $max_date, $cid);
            $notification_status['admin_travel_disapproved'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_disaproved, $view_status, $min_date, $max_date, $cid);
            $notification_status['admin_travel_resubmit'] = Employee::Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_resubmit, $view_status, $min_date, $max_date, $cid);

            $notification_total['leave_total'] = $notification_status['leave_approved'] + $notification_status['leave_disapproved'] + $notification_status['leave_resubmit'];
            $notification_total['expense_total'] = $notification_status['expense_approved'] + $notification_status['expense_disapproved'] + $notification_status['expense_resubmit'];
            $notification_total['travel_total'] = $notification_status['travel_approved'] + $notification_status['travel_disapproved'] + $notification_status['travel_resubmit'] +
                $notification_status['admin_travel_approved'] + $notification_status['admin_travel_disapproved'] + $notification_status['admin_travel_resubmit'];

        } elseif ($role == 2) {
            $notification_total['leave_total'] = Employee::getAppNotificationCountByModule($cid, $module = "leave", $role = "LM", $user_id);
            $notification_total['expense_total'] = Employee::getAppNotificationCountByModule($cid, $module = "expense", $role = "LM", $user_id);
            $notification_total['travel_total'] = Employee::getAppNotificationCountByModule($cid, $module = "travel", $role = "LM", $user_id);

        } elseif ($role == 3) {

            $notification_total['leave_total'] = Employee::getAppNotificationCountByModule($cid, $module = "leave", $role = "HR", $user_id);
            $notification_total['expense_total'] = Employee::getAppNotificationCountByModule($cid, $module = "expense", $role = "HR", $user_id);
            $notification_total['travel_total'] = Employee::getAppNotificationCountByModule($cid, $module = "travel", $role = "HR", $user_id);

        } else if ($role == 4) {
            $users_ids = Employee::GetUserListByHR($cid);

            $notification_total['travel_total'] = Employee::getAdminTravelNotificationCount($users_ids, $cid, $min_date, $max_date);
        } else {
            return 0;
        }

//        $notification_total['attendance_total']=0;
        //        $notification_total['loan_total']=0;
        //        $notification_total['apprisal_total']=0;
        return $notification_total;
    }

    public static function travel_recived_status_hr($user_id, $lm_status, $hr_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $user_id)
            ->where('lm_status', $lm_status)
            ->where('hr_status', $hr_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_status_admin($user_id, $hr_status, $admin_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $user_id)
            ->where('hr_status', $hr_status)
            ->where('admin_status', $admin_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function expense_recived_status_employee($users_ids, $lm_status, $min_date, $max_date, $cid)
    {

//        DB::enableQueryLog();

        $value = DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->leftjoin('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->where('proll_expense.eid', $users_ids)
            ->where('proll_expense.hr_status', $lm_status)
            ->where('proll_expense.hr_status', '!=', '4')
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense.exp_date', '>=', $min_date)
            ->where('proll_expense.exp_date', '<=', $max_date)
            ->groupBy('proll_expense.id')
            ->select('proll_expense.id')
            ->get();

        return count($value);
    }

    public static function pending_expense_count_by_status($users_ids, $lm_status, $min_date, $max_date, $cid)
    {
        $value = DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->leftjoin('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->where('proll_expense.eid', $users_ids)
            ->where(function ($query) use ($lm_status) {
                $query->whereIn('proll_expense.lm_status', [$lm_status, 4]);
                $query->orWhereIn('proll_expense.hr_status', [$lm_status, 4]);
            })
            ->where(function ($query) use ($lm_status) {
                $query->where('proll_expense.lm_status', '!=', 3);
                $query->where('proll_expense.hr_status', '!=', 3);
            })
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense.exp_date', '>=', $min_date)
            ->where('proll_expense.exp_date', '<=', $max_date)
            ->groupBy('proll_expense.id')
            ->select('proll_expense.id')
            ->get();
        return count($value);
    }

    public static function disapproved_expense_count_by_status($users_ids, $lm_status, $min_date, $max_date, $cid)
    {
        $value = DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->leftjoin('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->where('proll_expense.eid', $users_ids)
            ->where(function ($query) use ($lm_status) {
                $query->whereIn('proll_expense.lm_status', [$lm_status]);
                $query->orWhereIn('proll_expense.hr_status', [$lm_status]);
            })
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense.exp_date', '>=', $min_date)
            ->where('proll_expense.exp_date', '<=', $max_date)
            ->groupBy('proll_expense.id')
            ->select('proll_expense.id')
            ->get();

        return count($value);
    }

    public static function expense_recived_status_lm($users_ids, $lm_status, $min_date, $max_date, $cid)
    {

        $value = DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->join('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->whereIn('proll_expense.eid', $users_ids)
            ->where('proll_expense.lm_status', $lm_status)
            ->where('proll_expense.hr_status', '!=', '4')
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense.exp_date', '>=', $min_date)
            ->where('proll_expense.exp_date', '<=', $max_date)
            ->groupBy('proll_expense.id')
            ->select('proll_expense.id')
            ->get();
        return count($value);
    }

    public static function expense_recived_status_hr($users_id, $lm_status, $hr_status, $min_date, $max_date, $cid)
    {

        $value = DB::table('proll_expense')
            ->join('proll_employee', 'proll_employee.id', '=', 'proll_expense.eid')
            ->join('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->whereIn('proll_expense.eid', $users_id)
            ->where('proll_expense.lm_status', $lm_status)
            ->where('proll_expense.hr_status', $hr_status)
            ->where('proll_expense.hr_status', '!=', 4)
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense_detail.exp_date', '>=', $min_date)
            ->where('proll_expense_detail.exp_date', '<=', $max_date)
            ->groupBy('proll_expense.id')
            ->select('proll_expense.id')
            ->get();
        return count($value);
    }

    public static function travel_recived_HR_admin_view($users_ids, $lm_status, $hr_status, $admin_status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $users_ids)
            ->where('lm_status', $lm_status)
            ->where('hr_status', $hr_status)
            ->where('admin_status', $admin_status)
            ->where('hr_view_status', $view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_HR_HR_view($users_ids, $approved_status, $status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $users_ids)
            ->where('lm_status', $approved_status)
            ->where('hr_status', $status)
            ->where('hr_view_status', $view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_Admin_view($users_ids, $approved_status, $status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $users_ids)
            ->where('hr_status', $approved_status)
            ->where('hr_status', $status)
            ->where('admin_view_status', $view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function expense_recived_HR_HR_view($users_ids, $lm_status, $hr_status, $view_status, $min_date, $max_date, $cid)
    {
        return Expense::whereIn('eid', $users_ids)
            ->where('lm_status', $lm_status)
            ->where('cid', $cid)
            ->where('hr_view_status', $view_status)
            ->where('hr_status', $hr_status)
            ->where('added_on', '>=', $min_date)
            ->where('added_on', '<=', $max_date)
            ->count();
    }

    public static function leave_recive_HR_HR_view($users_id, $lm_approved, $view_status, $min_date, $max_date, $cid)
    {
        return Leave::whereIn('empid', $users_id)
            ->where('appstatus', $lm_approved)
            ->where('clientid', $cid)
            ->where('hr_view_status', $view_status)
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->count();
    }

    public static function Travel_view_recived_Admin($user_id, $lm_approved, $hr_approved, $admin_status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::where('emp_id', $user_id)
            ->where('lm_status', $lm_approved)
            ->where('hr_status', $hr_approved)
            ->where('admin_status', $admin_status)
            ->where('view_status', $view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function Travel_view_recived_HR($user_id, $lm_approved, $hr_status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::where('emp_id', $user_id)
            ->where('lm_status', $lm_approved)
            ->where('hr_status', $hr_status)
            ->where('view_status', $view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function Travel_view_recived_LM($user_id, $lm_status, $view_status, $min_date, $max_date, $cid)
    {
        return Travel::where('emp_id', $user_id)
            ->where('lm_status', $lm_status)
            ->where('client_id', $cid)
            ->where('view_status', $view_status)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function Get_all_travel_views_count($user_id, $lm_status, $view_status, $min_date, $max_date, $cid)
    {
//          DB::enableQueryLog();
        $travel = Travel::where('emp_id', $user_id)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->where(function ($query) {
                $query->where('view_status', '=', 0)
                    ->orWhere('lm_view_status', '=', 0)
                    ->orWhere('hr_view_status', '=', 0)
                    ->orWhere('admin_view_status', '=', 0);
            })
            ->count();
//            $query = DB::getQueryLog();
        //            print_r($query);
        //            die;
        //        var_dump($travel);die;
        return $travel;
    }

    public static function Expense_view_recived_HR($user_id, $lm_approved, $hr_status, $view_status, $min_date, $max_date, $cid)
    {
        return Expense::where('eid', $user_id)
            ->where('lm_status', $lm_approved)
            ->where('cid', $cid)
            ->where('view_status', $view_status)
            ->where('hr_status', $hr_status)
            ->where('added_on', '>=', $min_date)
            ->where('added_on', '<=', $max_date)
            ->count();
    }

    public static function Expense_view_recived($user_id, $lm_status, $view_status, $min_date, $max_date, $cid)
    {
        return Expense::where('eid', $user_id)
            ->where('lm_status', $lm_status)
            ->where('view_status', $view_status)
            ->where('cid', $cid)
            ->where('added_on', '>=', $min_date)
            ->where('added_on', '<=', $max_date)
            ->count();
    }

    public static function leave_view_recived($user_id, $status, $view_status, $min_date, $max_date, $cid)
    {

//        DB::enableQueryLog();
        $leave = Leave::where('empid', $user_id)
            ->where('appstatus', $status)
            ->where('view_status', $view_status)
            ->where('clientid', $cid)
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->count();
//    var_dump($user_id,$status,$view_status,$min_date,$max_date,$cid);
        //        $query = DB::getQueryLog();
        //        print_r($query);
        //        dd($leave);
        return $leave;
    }

    public static function leave_recived_LM_view($user_ids, $status, $lm_view_status, $min_date, $max_date, $cid)
    {
        return Leave::whereIn('empid', $user_ids)
            ->where('appstatus', $status)
            ->where('clientid', $cid)
            ->where('lm_view_status', $lm_view_status)
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->count();
    }

    public static function expense_recived_LM_view($user_id, $status, $lm_view_status, $min_date, $max_date, $cid)
    {
        return DB::table('proll_expense')
            ->join('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->whereIn('proll_expense.eid', $user_id)
            ->where('proll_expense.lm_status', $status)
            ->where('proll_expense.lm_view_status', $lm_view_status)
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense_detail.exp_date', '>=', $min_date)
            ->where('proll_expense_detail.exp_date', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_LM_view($user_id, $status, $lm_view_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $user_id)
            ->where('lm_status', $status)
            ->where('lm_view_status', $lm_view_status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function expense_recived_HR_view($user_id, $lm_status, $hr_status, $lm_view_status, $min_date, $max_date, $cid)
    {

        return Expense::whereIn('eid', $user_id)
            ->where('lm_status', $lm_status)
            ->where('cid', $cid)
            ->where('hr_status', $hr_status)
            ->where('lm_view_status', $lm_view_status)
            ->where('added_on', '>=', $min_date)
            ->where('added_on', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_HR_view($user_id, $lm_status, $hr_status, $hr_view_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $user_id)
            ->where('lm_status', $lm_status)
            ->where('client_id', $cid)
            ->where('hr_status', $hr_status)
            ->where('hr_view_status', $hr_view_status)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function leave_recived($user_ids, $status, $min_date, $max_date, $cid)
    {

        return Leave::whereIn('empid', $user_ids)
            ->where('appstatus', $status)
            ->where('clientid', $cid)
            ->where('fdate', '>=', $min_date)
            ->where('tdate', '<=', $max_date)
            ->count();
    }

    public static function expense_recived($user_id, $status, $min_date, $max_date, $cid)
    {
        return DB::table('proll_expense')
            ->join('proll_expense_detail', 'proll_expense.id', '=', 'proll_expense_detail.exp_id')
            ->whereIn('proll_expense.eid', $user_id)
            ->where('proll_expense.hr_status', $status)
            ->where('proll_expense.temp', 0)
            ->where('proll_expense.cid', $cid)
            ->where('proll_expense_detail.exp_date', '>=', $min_date)
            ->where('proll_expense_detail.exp_date', '<=', $max_date)
            ->count();
    }

    public static function travel_recived($user_id, $status, $min_date, $max_date, $cid)
    {

        return Travel::whereIn('emp_id', $user_id)
            ->where('lm_status', $status)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function travel_recived_HR($user_id, $lm_status, $hr_status, $min_date, $max_date, $cid)
    {
        return Travel::whereIn('emp_id', $user_id)
            ->where('lm_status', $lm_status)
            ->where('client_id', $cid)
            ->where('hr_status', $hr_status)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->count();
    }

    public static function expense_recived_HR($user_id, $lm_status, $hr_status, $min_date, $max_date, $cid)
    {
        return Expense::whereIn('eid', $user_id)
            ->where('lm_status', $lm_status)
            ->where('cid', $cid)
            ->where('hr_status', $hr_status)
            ->where('added_on', '>=', $min_date)
            ->where('added_on', '<=', $max_date)
            ->count();
    }

    public static function RosterMonthlyAttendance($emp_id, $client_id, $date)
    {

        $start_date = date("Y-m-01", strtotime("0 month", strtotime($date)));
        $end_date = date("Y-m-t", strtotime("0 month", strtotime($date)));
        $dates = User::getFiscalYearDatesByModule('Leave Add');
        $min_date = $dates->mini;
        $max_date = $dates->maxi;
        if ($min_date >= $start_date && $max_date >= $end_date) {
            $quota_start_year = $start_date;
            $quota_end_year = $end_date;
        } else {
            $quota_start_year = $min_date;
            $quota_end_year = $max_date;
        }

        $roster = DB::select("
    SELECT DISTINCT  `ss`.`name` AS `shift_name`, `ss`.`color`,`ss`.`late_arrival_02_mins` AS `late`,
    `r`.`plan_shift_time_in` AS `plan_start_time`, `r`.`plan_shift_time_out` AS `plan_end_time`,
    `r`.`roster_date`,`r`.`actual_shift_time_in` AS `actual_start_time`,
    `r`.`actual_shift_time_out` AS `actual_end_time`,`t`.`name` AS `type`,`l`.`nod` AS `nod`,
    `ss`.`late_arrival_01_mins` AS `tolerance`,
    `ss`.`late_arrival_01_mins_afc` AS `afc`
    FROM proll_employee AS `e`
    LEFT JOIN `al_roster` AS `r` ON `e`.`id` = `r`.`emp_id`
    LEFT JOIN `al_shift_setup` AS `ss` ON `ss`.`shift_id` = `r`.`shift_id`
    LEFT JOIN `proll_leave` AS `l` ON `r`.`emp_id` = `l`.`empid` AND `r`.`roster_date` >= `l`.`fdate`
    AND `r`.`roster_date` <= `l`.`tdate` AND `l`.`appstatus` <= '2'
    LEFT JOIN `proll_leave_type_c` AS `t` ON `l`.`type_id` = `t`.`id`
    WHERE `r`.`emp_id` ='$emp_id'
    AND `r`.`cid` ='$client_id' AND `r`.`roster_date`
    BETWEEN '$start_date' AND '$end_date' ORDER BY `r`.`roster_date` ASC
");

//--- Fetching Holidays Between Start and End Date ---
        $month_holidays = DB::select("SELECT`hdesc`,`start_date`,`end_date`
    FROM `holidays`
    WHERE `start_date` BETWEEN '$quota_start_year' AND '$quota_end_year'
    OR `end_date`  BETWEEN '$quota_start_year' AND '$quota_end_year'
");

        $holidays['date'] = array();
        $holidays['name'] = array();

        if (count($month_holidays) > 0) {
            foreach ($month_holidays as $sub_arrs) {
                $sub_arr = (array) $sub_arrs;
                //--- If Holiday more then one day ---
                if (strtotime($sub_arr['start_date']) != strtotime($sub_arr['end_date'])) {
                    while (strtotime($sub_arr['start_date']) <= strtotime($sub_arr['end_date'])) {
                        $holidays['date'][] = $sub_arr['start_date'];
                        $holidays['name'][] = $sub_arr['hdesc'];
                        $sub_arr['start_date'] = date("Y-m-d", strtotime("+1 day", strtotime($sub_arr['start_date'])));
                    }
                } else {
                    $holidays['date'][] = $sub_arr['start_date'];
                    $holidays['name'][] = $sub_arr['hdesc'];
                }
            }
        }

//--- Fetching Travel Between Start and End Date ---
        $month_travel = DB::select("
    SELECT  `t`.`reasons`, min(`date`) AS `start_date`, max(`date`) AS `end_date`
    FROM `proll_travel` AS `t`
    INNER JOIN `proll_travel_detail` AS `td` ON `t`.`id` = `td`.`travel_id`
    WHERE `t`.`hr_status` <> '3' AND `t`.`lm_status` <> '3'
    AND `t`.`admin_status` <> '3'
    AND `td`.`date` BETWEEN '$quota_start_year' AND '$quota_end_year'
    AND `t`.`emp_id` = '$emp_id'
    GROUP BY `td`.`travel_id`
");

        $travels['date'] = array();
        $travels['name'] = array();

        if (count($month_travel) > 0) {

            foreach ($month_travel as $sub_arrs) {
                $sub_arr = (array) $sub_arrs;

                //--- If Travel Holiday more then one day ---
                if (strtotime($sub_arr['start_date']) != strtotime($sub_arr['end_date'])) {
                    while (strtotime($sub_arr['start_date']) <= strtotime($sub_arr['end_date'])) {
                        $travels['date'][] = $sub_arr['start_date'];
                        $travels['reason'][] = $sub_arr['reasons'];
                        $sub_arr['start_date'] = date("Y-m-d", strtotime("+1 day", strtotime($sub_arr['start_date'])));
                    }
                } else {
                    $travels['date'][] = $sub_arr['start_date'];
                    $travels['reason'][] = $sub_arr['reasons'];
                }
            }
        }

        $row = array();
        $past_public_holidays = array();
        $past_travel = array();
        $repeat_check = array();
        $date_array = array();
        foreach ($roster as $key => $values) {
            $value = (array) $values;
            if ($value['actual_start_time'] != '0000-00-00 00:00:00') {
                $time_diff = strtotime($value['actual_start_time']) - strtotime($value['plan_start_time']);
                $time_diff = round(($time_diff) / 60, 2);
                if ($time_diff < 1) {
                    $time_diff = 1;
                }
            } else {
                $time_diff = 0;
            }
            $time_diff = intval($time_diff);

            $roster_date = $value['roster_date'];
            $time_in = '';
            if ($value['type'] != null) {
                if ($value['type'] == 'Work Leave') {

                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                    $row['reason'] = 'Work Leave';
                    $row['color'] = '#a1ffcc';
//                     "[" . date('Y,m,d', strtotime($roster_date)) . ",'Work Leave', '#a1ffcc']";
                } else {
//                    $row = "[" . date('Y,m,d', strtotime($roster_date)) . ",'" . $value['type'] . "', '#d5ffa8']";
                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                    $row['reason'] = $value['type'];
                    $row['color'] = '#d5ffa8';
                }
            } else {
                if ($time_diff > ($value['tolerance'] + 1) && $time_diff <= $value['late']) {
//                    $row = "[" . date('Y,m,d', strtotime($roster_date)) . ",'Late Arrival - 9:10 " . $time_in . "', '#c8ecff']";

                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                    $row['reason'] = 'Late Arrival - 9:10 ' . $time_in;
                    $row['color'] = '#c8ecff';
                } else {
                    if ($time_diff > $value['late']) {
//                        $row = "{" . date('Y,m,d', strtotime($roster_date)) . ",'Late Arrival - 9:30 (Absent)', '#ffdff6']";
                        $row['date'] = date('Y-m-d', strtotime($roster_date));
                        $row['reason'] = 'Late Arrival - 9:30 (Absent)';
                        $row['color'] = '#c8ecff';
                    } else {
                        if ($time_diff == 0) {
                            if (in_array($roster_date, $holidays['date'])) {
                                $past_public_holidays[] = $roster_date;
                                $hname = $holidays['name'][array_search($roster_date, $holidays['date'])];
//                                $row = "[" . date('Y,m,d', strtotime($roster_date)) . ",'" . $hname . "', '#fdcc8a']";

                                $row['date'] = date('Y-m-d', strtotime($roster_date));
                                $row['reason'] = $hname;
                                $row['color'] = '#fdcc8a';
                            } else {
                                if (in_array($roster_date, $travels['date'])) {
                                    $past_travel[] = $roster_date;
//                                    $row ="[" . date('Y,m,d', strtotime($roster_date)) . ",'Business Travel', '#cdaed2']";
                                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                                    $row['reason'] = 'Business Travel';
                                    $row['color'] = '#cdaed2';
                                } elseif (strtotime($roster_date) > strtotime(date('Y-m-d'))) {
                                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                                    $row['reason'] = 'N/A';
                                    $row['color'] = '#00FFFFFF';
                                } else {
//                                    $row = "[" . date('Y,m,d', strtotime($roster_date)) . ",'Absent', '#fe9a9a']";
                                    $row['date'] = date('Y-m-d', strtotime($roster_date));
                                    $row['reason'] = 'Absent';
                                    $row['color'] = '#fe9a9a';
                                }
                            }
                        }
                    }
                }
            }
            if ($row) {
                $date_array[] = $row;
            }
        }

        foreach ($holidays['date'] as $key => $val) {

            if ($start_date <= $val && $val <= $end_date) {
                if (!in_array($val, $past_public_holidays)) {

//                $row = "[" . date('Y,m,d', strtotime($val)) . ",'" . $holidays['name'][$key] . "', 'public-holiday']";
                    $row['date'] = date('Y-m-d', strtotime($val));
                    $row['reason'] = $holidays['name'][$key];
                    $row['color'] = '#fdcc8a';
                    if ($row) {
                        $date_array[] = $row;
                    }
                }
            }
        }

        foreach ($travels['date'] as $key => $val) {
            if (!in_array($val, $past_travel)) {
//                $row = "[" . date('Y,m,d', strtotime($val)) . ",'" . $travels['reason'][$key] . "', 'travel']";

                $row['date'] = date('Y-m-d', strtotime($val));
                $row['reason'] = $travels['reason'][$key];
                $row['color'] = '#cdaed2';
                if ($row) {
                    $date_array[] = $row;
                }
            }
        }

        $period = CarbonPeriod::create($start_date, $end_date);
        $count = 0;

        foreach ($period as $date) {
            if ($count != 0) {
                $find = Employee::searchForId($date->format('Y-m-d'), $date_array);

                if ($find == null) {
                    $row['date'] = $date->format('Y-m-d');
                    $row['reason'] = '';
                    $row['color'] = '#00FFFFFF'; // HASSAN
                    //                    $row['color'] =''; NOUMAN
                    if ($row) {
                        $date_array[] = $row;
                    }
                }
            }

            $count++;
        }

        $result = array();
        foreach ($date_array as $key => $value) {
            if (!in_array($value, $result)) {
                $result[] = $value;
            }

        }

        usort($result, function ($a, $b) {
            return new Carbon($a['date']) <=> new Carbon($b['date']);
        });
        return $result;
    }

    public static function searchForId($date, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['date'] === $date) {
                return $key;
            }
        }
        return null;
    }
    //For HLS_2.3
    public static function User_Roles($user_id, $cid)
    {
        return DB::table('user_roles')
            ->join('group_roles', 'group_roles.id', '=', 'user_roles.group_role_id')
            ->join('roles', 'roles.id', '=', 'group_roles.id')
            ->where('user_roles.cid', $cid)
            ->where('user_roles.user_id', $user_id)
            ->select('roles.id', 'roles.name')
            ->get();
    }
    public static function available_modules()
    {
        return DB::table('modules')->select('id', 'name', 'status')->get();
    }
    public static function User_Roles_v2_3($user_id, $cid)
    {
        return DB::table('user_roles')
            ->join('group_roles', 'group_roles.id', '=', 'user_roles.group_role_id')
            ->join('roles', 'roles.id', '=', 'group_roles.roles_portal_id')
            ->where('user_roles.cid', $cid)
            ->where('user_roles.user_id', $user_id)
            ->select('roles.id', 'roles.name')
            ->get();
    }

    //For HLS_2.3
    public static function check_user_Roles($user_id)
    {
        return DB::table('user_roles')
            ->join('group_roles', 'group_roles.id', '=', 'user_roles.group_role_id')
            ->join('roles', 'roles.id', '=', 'group_roles.roles_portal_id')
            ->where('user_roles.user_id', $user_id)
            ->pluck('roles.id')
            ->toArray();
    }

    public static function HR_Employee_List($client_id)
    {
        return DB::table('proll_employee')
            ->join('proll_department', 'proll_department.id', '=', 'proll_employee.dept_id')
            ->where('proll_employee.cid', $client_id)
            ->select('proll_department.department', 'proll_employee.name', 'proll_employee.empcode', 'proll_employee.loginname',
//                DB::raw("CONCAT('".$_ENV['BASE_URL']."'/emp_pictures/',proll_employee.picture) AS picture")
                DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
            )
            ->get();
    }

    public static function EmployeeProfile($user_id)
    {
        return DB::table('proll_employee')
            ->join('proll_department', 'proll_department.id', '=', 'proll_employee.dept_id')
            ->where('proll_employee.id', $user_id)
            ->select('proll_employee.id', 'proll_department.department', 'proll_employee.name', 'proll_employee.empcode', 'proll_employee.loginname',
//                DB::raw("CONCAT('".$_ENV['BASE_URL']."'/emp_pictures/',proll_employee.picture) AS picture")
                DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
            )
            ->first();
    }

    public static function DepartmentsList()
    {
        return DB::table('proll_department')
            ->join('proll_employee', 'proll_department.id', '=', 'proll_employee.dept_id')
            ->where('proll_employee.status', 1)
            ->select('proll_department.department', 'proll_department.id as department_id', 'proll_department.line_manager', DB::raw('COUNT(proll_employee.id) as head_count'))
            ->groupBy('proll_department.id')
            ->get();
    }

    public static function DepartmentsEmployeeList($dept_id, $user_id)
    {
//            DB::enableQueryLog();

        $results = DB::table('proll_employee')
            ->join('proll_department', 'proll_department.id', '=', 'proll_employee.dept_id')
            ->join('proll_client_designation', 'proll_client_designation.designation_id', '=', 'proll_employee.designation')
//                ->where('proll_employee.dept_id', $dept_id)
            ->where('proll_employee.status', 1)
            ->where('proll_employee.id', '!=', $user_id)
            ->where(function ($query) use ($dept_id) {
                $query->where('proll_employee.dept_id', $dept_id)
                    ->where('proll_employee.reporting_to_id', 0)
                    ->orwhere('proll_employee.reporting_to_id', $dept_id);
            })
            ->select('proll_employee.id', 'proll_department.department', 'proll_employee.name', 'proll_employee.empcode', 'proll_employee.loginname', 'proll_client_designation.designation_name', DB::raw('(CASE WHEN proll_employee.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",proll_employee.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
            )
            ->get();
//                            $query = DB::getQueryLog();
        //                            print_r($query);
        foreach ($results as $result) {
            $result->id = encrypt($result->id);
        }

        return $results;
    }
    public static function getEmployeesBasicInfo($client_id, $status = 1)
    {
        $results = DB::table('proll_employee as e')
            ->join('proll_department as d', 'd.id', '=', 'e.dept_id')
            ->join('proll_client_designation as desig', 'desig.designation_id', '=', 'e.designation')
            ->leftjoin('employee_bands as b', 'e.emp_band', '=', 'b.id')
            ->leftjoin('proll_department as d1', function ($join) {
                $join->on('d1.id', '=', DB::raw('CASE
                                        WHEN e.reporting_to_id=0 THEN e.dept_id ELSE e.reporting_to_id
                                        END'));
            })
            ->leftjoin('al_shift_setup as s', function ($join) {
                $join->on('s.shift_id', '=', DB::raw('CASE
                                        WHEN e.shift_id=0 THEN e.default_shift_id ELSE e.shift_id
                                        END'));
            })
            ->where('e.status', $status)
            ->where('e.cid', $client_id)
            ->select('e.id', 'e.empcode', 'd.department', 'e.name', 'e.loginname as email', 'desig.designation_name as designation',
                'b.band_desc as band', 'd1.line_manager AS reporting_to', 's.name as shift_name', 's.time_in', 's.time_out',
                DB::raw('(CASE WHEN e.picture IS NOT NULL THEN  CONCAT("' . env('BASE_URL') . '/emp_pictures/",e.picture) ELSE "' . env('BASE_URL') . '/emp_pictures/favicon.png" END) AS profile')
            )
            ->get();
        return $results;
    }
    public static function RosterEmployees($dept_id)
    {
        $roster_employees = DB::table('proll_employee')
            ->leftJoin('proll_department', 'proll_department.id', '=', 'proll_employee.dept_id')
            ->leftJoin('al_roster_overtime_cpl as alrcpl', 'alrcpl.emp_id', '=', 'proll_employee.id')
            ->where(['dept_id' => $dept_id, 'applyed_roster' => 1])
            ->select('proll_employee.name as roster_employee_name', 'proll_department.department as employee_department',
                DB::raw('ifnull(alrcpl.overtime,0) as employee_overtime'),
                DB::raw('ifnull(alrcpl.cpl,0) as employee_cpl')
            )
            ->get();
        return $roster_employees;
    }
    public static function ShiftRosterDetails($dept_id, $date, $shift_id)
    {
        $shift_roster_detail = DB::table('al_roster')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('department_hierarchy', 'department_hierarchy.id', '=', 'al_roster.dept_id')
            ->leftJoin('al_shift_setup', 'al_shift_setup.shift_id', '=', 'al_roster.shift_id')
            ->where(['al_roster.dept_id' => $dept_id, 'al_roster.roster_date' => $date, 'al_roster.shift_id' => $shift_id])
            ->select('proll_employee.name as employee_name', 'department_hierarchy.department_name', 'al_shift_setup.name as shift_name',
                'al_roster.roster_date as date', DB::raw("DATE_FORMAT(al_roster.actual_shift_time_in, '%H:%i') as Time_In"),
                DB::raw("DATE_FORMAT(al_roster.actual_shift_time_out, '%H:%i') as Time_Out"))
            ->get();
        return $shift_roster_detail;
    }
    public static function EmployeeRosterForLM($emp_id, $date_from, $date_to)
    {
        $roster = DB::table('al_roster')
            ->leftJoin('proll_department', 'proll_department.id', '=', 'al_roster.dept_id')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('al_shift_setup', 'al_shift_setup.shift_id', '=', 'al_roster.shift_id')
            ->where('al_roster.emp_id', $emp_id)
            ->whereBetween('al_roster.roster_date', array($date_from, $date_to))
            ->select('proll_employee.name as employee_name', 'proll_department.department as department_name', 'al_shift_setup.name as shift_name',
                DB::raw('DATE_FORMAT(al_roster.roster_date, "%W, %e %M %Y") as selected_date'), 'al_roster.plan_shift_time_in as plan_time_in',
                'al_roster.plan_shift_time_out as plan_time_out', 'al_roster.actual_shift_time_in as actual_time_in', 'al_roster.actual_shift_time_out as actual_time_out')
            ->get();
        return $roster;
    }
    public static function getEmployeeRoster($emp_id, $date_from, $date_to)
    {
        $roster = DB::table('al_roster')
            ->leftJoin('department_hierarchy', 'department_hierarchy.id', '=', 'al_roster.dept_id')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('al_shift_setup', 'al_shift_setup.shift_id', '=', 'al_roster.shift_id')
            ->where('al_roster.emp_id', $emp_id)
            ->whereBetween('al_roster.roster_date', array($date_from, $date_to))
            ->select('proll_employee.name as employee_name', 'department_hierarchy.department_name', 'al_shift_setup.name as shift_name',
                DB::raw('DATE_FORMAT(al_roster.roster_date, "%W, %e %M %Y") as selected_date'), 'al_roster.plan_shift_time_in as plan_time_in',
                'al_roster.plan_shift_time_out as plan_time_out', 'al_roster.actual_shift_time_in as actual_time_in', 'al_roster.actual_shift_time_out as actual_time_out')
            ->get();
        return $roster;
    }
    public static function EmployeeRosterForEmployee($emp_id, $date_from, $date_to)
    {
//          DB::enableQueryLog();
        $roster = DB::table('al_roster')
            ->leftJoin('proll_department_managers', 'proll_department_managers.id', '=', 'al_roster.created_by')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->leftJoin('al_shift_setup', 'al_shift_setup.shift_id', '=', 'al_roster.shift_id')
            ->where('al_roster.emp_id', $emp_id)
            ->whereBetween('al_roster.roster_date', array($date_from, $date_to))
            ->select('proll_employee.name as employee_name', 'proll_department_managers.department as department_name', 'al_shift_setup.name as shift_name',
                DB::raw('DATE_FORMAT(al_roster.roster_date, "%W, %e %M %Y") as selected_date'), 'al_roster.plan_shift_time_in as plan_time_in',
                'al_roster.plan_shift_time_out as plan_time_out', 'al_roster.actual_shift_time_in as actual_time_in', 'al_roster.actual_shift_time_out as actual_time_out')
            ->get();
//          $query = DB::getQueryLog();
        //            dd($query);
        return $roster;
    }
    public static function DepartmentShifts($dept_id)
    {
        $dept_shifts = DB::table('al_shift_setup')
            ->leftJoin('al_roster', 'al_roster.shift_id', '=', 'al_shift_setup.shift_id')
            ->leftJoin('proll_department_managers', 'proll_department_managers.id', '=', 'al_roster.dept_id')
            ->where('al_roster.dept_id', $dept_id)
            ->distinct('al_roster.shift_id')
            ->select('al_shift_setup.name as shift_name', 'al_shift_setup.time_in as shift_time_in', 'al_shift_setup.time_out as shift_time_out')
            ->get();
        return $dept_shifts;
    }
    public static function getDepartmentShifts($dept_id)
    {
        $dept_shifts = DB::table('al_shift_setup')
            ->leftJoin('proll_filter_table', 'proll_filter_table.table_id', '=', 'al_shift_setup.shift_id')
            ->where('proll_filter_table.dept_id', $dept_id)
            ->select('al_shift_setup.name as shift_name', 'al_shift_setup.time_in as shift_time_in', 'al_shift_setup.time_out as shift_time_out')
            ->get();
        return $dept_shifts;
    }
    public static function UpdateRosterTime($emp_id, $roster_date, $time_in, $time_out)
    {
        /**
         * Select Employee code by employee id
         */

        DB::beginTransaction();
        try {
            $employee_code = DB::table('proll_employee')->where('id', $emp_id)->value('empcode');

            DB::table('al_roster')
                ->where(['emp_id' => $emp_id, 'roster_date' => $roster_date])
                ->update(
                    [
                        'actual_shift_time_in' => $time_in,
                        'actual_shift_time_out' => $time_out,
                    ]
                );

            DB::table('attendance')
                ->insert([
                    [
                        'date' => date('Y-m-d', strtotime($time_in)),
                        'time' => date('H:i:s', strtotime($time_in)),
                        'emp_code' => $employee_code,
                        'type' => 'Time In',
                        'status' => '0',
                        'remarks' => 'N/A',
                        'location' => 'Mobile',
                        'date_time' => $time_in,
                    ],
                    [
                        'date' => date('Y-m-d', strtotime($time_out)),
                        'time' => date('H:i:s', strtotime($time_out)),
                        'emp_code' => $employee_code,
                        'type' => 'Time Out',
                        'status' => '1',
                        'remarks' => 'N/A',
                        'location' => 'Mobile',
                        'date_time' => $time_out,
                    ],
                ]);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }
    public static function AddCPL($emp_id, $updated_cpl, $start_date, $end_date)
    {
//        DB::table('al_roster_overtime_cpl')
        //            ->where('emp_id', $emp_id)
        //            ->update(['cpl' => $updated_cpl]);
        DB::table('al_roster_overtime_cpl')
            ->insert([
                'emp_id' => $emp_id,
                'overtime' => '0',
                'cpl' => $updated_cpl,
                'duration' => 'Custom',
                'source' => 'LM',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'created_at' => date("Y-m-d"),
                'updated_at' => '',
            ]);
        return true;

    }
    public static function RosterRequests($line_manager)
    {
        $roster_requests = DB::table('al_roster_requests')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster_requests.emp_id')
            ->leftJoin('roster_request_types', 'roster_request_types.id', '=', 'al_roster_requests.request_type_id')
            ->where('line_manager', $line_manager)
            ->select('proll_employee.id as emp_id', 'proll_employee.name as employee_name', DB::raw('DATE_FORMAT(request_date, "%e %M %Y") as Date_On'),
                DB::raw('DATE_FORMAT(change_with_date, "%e %M %Y") as Date_For'), 'roster_request_types.name as request_name',
                'al_roster_requests.status as Status')
            ->get();
        return $roster_requests;
    }
    public static function ShiftAllocatedEmployees($dept_id, $shift_id, $selected_date)
    {
        $employees = DB::table('al_roster')
            ->leftJoin('department_hierarchy', 'department_hierarchy.id', '=', 'al_roster.dept_id')
            ->leftJoin('proll_employee', 'proll_employee.id', '=', 'al_roster.emp_id')
            ->where(['al_roster.roster_date' => $selected_date, 'al_roster.dept_id' => $dept_id, 'al_roster.shift_id' => $shift_id])
            ->distinct('proll_employee.name')
            ->select('proll_employee.name as employee')
            ->get();
        return $employees;
    }
    public static function PersonalDetails($id)
    {
        $personal_details = DB::table('proll_employee as e')
            ->leftJoin('proll_employee_detail', 'proll_employee_detail.empid', '=', 'e.id')
            ->leftJoin('religion', 'religion.religion_id', '=', 'e.religion_id')
            ->leftJoin('languages', 'languages.language_id', '=', 'e.native_language_id')
            ->leftJoin('countries as bc', 'bc.country_id', '=', 'e.birth_country_id')
            ->leftJoin('cities as birth_city', 'birth_city.id', '=', 'e.birth_city_id')
            ->leftJoin('countries as dc', 'dc.country_id', '=', 'e.domicile_country_id')
            ->leftJoin('cities as domicile_city', 'domicile_city.id', '=', 'e.domicile_city_id')
            ->where('e.id', $id)
            ->select('e.name_salute', 'e.name',
                'e.f_hname',
                'e.father_occupation',
                'e.dob',
                'bc.country as birth_country',
                'birth_city.name as birth_city',
                'e.second_nationality_id',
                'dc.country as domicile_country',
                'domicile_city.name as domicile_city',
                'e.blood_group_name',
                'religion.religion_name',
                'languages.name as native_language',
                'e.cnic',
                'e.cnic_country_id',
                'e.cnic_issued_on',
                'e.cnic_expiry',
                'e.passport_no',
                'e.passport_country_id',
                'e.passport_issued_on',
                'e.passport_expiry',
                'e.driving_license_number',
                'e.driving_license_country_id',
                'e.driving_license_issued_on',
                'e.driving_license_expiry',
                'e.ntn_number',
                'e.ntn_country_id',
                'e.m_status',
                'e.no_of_dependants')
            ->first();
        return $personal_details;
    }

    /*************Employee Nominee Retrival API*********************/
    public static function getEmployeeNomineis($id)
    {
        return DB::table('proll_employee_dependents')->where('employee_id', $id)->get([
            'dependent_id as nominee_id',
            'dependent_name as name',
            'gender',
            'date_of_birth',
            'relationship',
            'cninc_number',
            'next_of_kins',
        ]);
    }
    /*************End of Employee Nominee Retrival API*********************/
    public static function EmploymentDetails($id)
    {
        $employment_details = DB::table('proll_employee')
            ->leftJoin('proll_department_managers as m', 'proll_employee.dept_id', '=', 'm.id')
            ->leftJoin('department_hierarchy as h', 'm.department_hierarchy_id', '=', 'h.id')
            ->leftJoin('proll_department_managers as m1', 'proll_employee.reporting_to_id', '=', 'm1.id')
            ->leftJoin('proll_department_managers as m2', 'proll_employee.second_reporting_to_id', '=', 'm2.id')
            ->leftJoin('proll_employee_detail', 'proll_employee_detail.empid', '=', 'proll_employee.id')
            ->leftJoin('employee_bands as b', 'b.id', '=', 'proll_employee.emp_band')
            ->leftJoin('proll_client as c', 'c.id', '=', 'proll_employee.cid')
            ->leftJoin('proll_hr_salaries as s', 's.emp_id', '=', 'proll_employee.id')
            ->leftJoin('proll_client_designation as d', 'd.designation_id', '=', 'proll_employee.designation')
            ->leftJoin('employee_bands', 'employee_bands.id', '=', 'proll_employee.emp_band')
            ->leftJoin('al_shift_setup', 'al_shift_setup.shift_id', '=', 'proll_employee.default_shift_id')
            ->leftJoin('proll_client_location', 'proll_client_location.loc_id', '=', 'proll_employee.loc_id')
            ->where('proll_employee.id', $id)
            ->select('proll_employee.name as employee_name',
                'proll_employee_detail.empcode as employee_code',
                'proll_employee_detail.empcode as people_code',
                'h.department_name',
                'd.designation_name as designation',
                'm.line_manager as department_lm',
                'm1.line_manager as external_lm_one',
                'm2.line_manager as external_lm_two',
                'proll_employee.loginname as official_email',
                'proll_employee.cell_number as official_mobile_no',
                'al_shift_setup.name as default_shift',
                's.gross_monthly_salary as gross_salary',
                DB::raw("(CASE WHEN proll_employee.applyed_roster=1 THEN 'Yes' ELSE 'No' END) as roster_applicable"),
                'c.companyname as company',
                'proll_client_location.loc_desc as branch_location',
                'b.band_desc as band',
                'proll_employee.doj as date_of_joining',
                DB::raw("(CASE WHEN proll_employee.status=1 THEN 'Active' ELSE 'Inactive' END) as employee_status"),
                'proll_employee.contract_start_date',
                'proll_employee.contract_end_date')
            ->first();

        return $employment_details;
    }
    public static function Education($id)
    {
        $education = DB::table('proll_employee_education')->where('employee_id', $id)->get([
            'employee_education_id',
            'name as degree_level',
            'discipline_name',
            'institute_name',
            'passing_year',
            'grade',
        ]);
        return $education;
    }
    public static function Skills($id)
    {
        $skills = DB::table('proll_employee_skills')
            ->leftJoin('skills', 'skills.skill_id', '=', 'proll_employee_skills.skill_id')
            ->where('proll_employee_skills.employee_id', $id)
            ->select('employee_skill_id', 'skills.name as skill_name', 'proll_employee_skills.skill_level')
            ->get();
        return $skills;
    }
    public static function Languages($id)
    {
        $languages = DB::table('proll_employee_language')
            ->leftJoin('languages', 'languages.language_id', '=', 'proll_employee_language.language_id')
            ->where('proll_employee_language.emp_id', $id)
            ->select('proll_employee_language.id as employee_language_id', 'languages.name as language_name', 'proll_employee_language.reading_level', 'proll_employee_language.writing_level', 'proll_employee_language.speaking_level')
            ->get();
        return $languages;
    }
    public static function ContactDetails($id)
    {

        return DB::table('proll_employee_contact')
            ->leftjoin('countries as c', 'c.country_id', '=', 'proll_employee_contact.country_id')
            ->leftjoin('states as s', 's.id', '=', 'proll_employee_contact.state_province_id')
            ->leftjoin('cities', 'cities.id', '=', 'proll_employee_contact.city_id')
            ->where(['emp_id' => $id])->get([
            DB::raw('proll_employee_contact.id as contact_id'),
            'address_type',
            'email',
            'mobile_number',
            'skype_name',
            'emergency_contact_person_name',
            'emergency_contact_number',
            'c.country',
            's.name as state_province',
            'cities.name as city',
            'address',
            'telephone_no',
            'zip_code',
        ]);
    }
    public static function References($id)
    {
        $references = DB::table('proll_employee_reference')->where('empid', $id)->get([
            DB::raw('reference_id'),
            'reference_person_name',
            'relation',
            'organization',
            'designation',
            'contact_no',
            'address',
            'email',
            'known_since',
        ]);
        return $references;
    }
    public static function BankDetails($id)
    {
        $bank_details = DB::table('bank_accounts as b')
            ->leftJoin('proll_currency', 'proll_currency.id', '=', 'b.currency_id')
            ->leftJoin('bank_branches', 'bank_branches.bank_branch_id', '=', 'b.bank_branch_id')
            ->where('b.employee_id', $id)
            ->select(DB::raw('b.bank_account_id as bank_detail_id'),
                'b.account_title',
                'b.account_number',
                'bank_branches.branch_name',
                'bank_branches.branch_code',
                'b.iban',
                'b.routing_code',
                'proll_currency.currency',
                'bank_branches.swift_code')
            ->first();
        return $bank_details;
    }
    public static function getEmployeeFlags($empid, $cid)
    {
        return DB::table('employee_flags')
            ->where('empid', $empid)
            ->where('cid', $cid)
            ->first();
    }

    public static function getEmployeeTravelNotificationCount($empid, $cid, $min_date, $max_date)
    {
        return DB::table('proll_travel')
            ->where('emp_id', $empid)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->where('view_status', 0)
            ->count();
    }

    public static function getLMTravelNotificationCount($empid, $cid, $min_date, $max_date)
    {
        return DB::table('proll_travel')
            ->whereIn('emp_id', $empid)
            ->where('client_id', $cid)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->where('lm_view_status', 0)
            ->count();
    }
    public static function getHRTravelNotificationCount($empid, $cid, $min_date, $max_date)
    {
        return DB::table('proll_travel')
            ->whereIn('emp_id', $empid)
            ->where('client_id', $cid)
            ->where('lm_status', '=', 2)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->where('hr_view_status', 0)
            ->count();
    }
    public static function getAdminTravelNotificationCount($empid, $cid, $min_date, $max_date)
    {
        return DB::table('proll_travel')
            ->whereIn('emp_id', $empid)
            ->where('client_id', $cid)
            ->where('hr_status', '=', 2)
            ->where('added', '>=', $min_date)
            ->where('added', '<=', $max_date)
            ->where('admin_view_status', 0)
            ->count();
    }

    /*********EMPLOYEE CONTACT DETAILS UPDATION API*********/
    public static function UpdateContactDetails($id, $contact_details)
    {

        foreach ($contact_details as $contact_detail) {
            $country_id = DB::table('countries')->where('country_id', $contact_detail['country_id'])->exists();
            $state_id = DB::table('states')->where(
                ['id' => $contact_detail['state_province_id'],
                    'country_id' => $contact_detail['country_id'],
                ])->exists();
            $city_id = DB::table('cities')->where(
                ['id' => $contact_detail['city_id'],
                    'state_id' => $contact_detail['state_province_id'],
                ])->exists();

            if (!$country_id) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Invalid country ID: ' . $contact_detail['country_id'],
                ], 400);
            } elseif (!$state_id) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Invalid state ID: ' . $contact_detail['state_province_id'],
                ], 400);
            } elseif (!$city_id) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Invalid city ID: ' . $contact_detail['city_id'],
                ], 400);
            } else {
                $data = [
                    'address_type' => $contact_detail['address_type'],
                    'email' => $contact_detail['email'],
                    'mobile_number' => $contact_detail['mobile_number'],
                    'skype_name' => $contact_detail['skype_name'],
                    'emergency_contact_person_name' => $contact_detail['emergency_contact_person_name'],
                    'emergency_contact_number' => $contact_detail['emergency_contact_number'],
                    'country_id' => $contact_detail['country_id'],
                    'state_province_id' => $contact_detail['state_province_id'],
                    'city_id' => $contact_detail['city_id'],
                    'address' => $contact_detail['address'],
                    'telephone_no' => $contact_detail['telephone_no'],
                    'zip_code' => $contact_detail['zip_code'],
                ];
                DB::table('proll_employee_contact')
                    ->where(['id' => $contact_detail['contact_id'], 'emp_id' => $id])
                    ->update($data);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Contact Details Updated Successfully',
        ], 200);

    }
    /***********END OF EMPLOYEE CONTACT DETAILS API***********/

    /*************Employee Work Experience Retrival API*********************/
    public static function GetWorkExperience($id)
    {
        return DB::table('proll_employee_job_history')->where('empid', $id)->get([
            'id as experienc_id',
            'designation',
            'organization',
            'job_from',
            'job_to',
            'salary',
            'reason_for_leave',
            'employer_contact_number',
            'employer_address',
        ]);
    }
    /*************End of Employee Work Experience Retrival API*********************/

    /*************** Update Employee Education *************************/
    public static function updateEducation($id, $education)
    {
        foreach ($education as $data) {
            DB::table('proll_employee_education')
                ->where(['employee_education_id' => $data['employee_education_id'], 'employee_id' => $id])
                ->update([
                    'name' => $data['degree_level'],
                    'discipline_name' => $data['discipline_name'],
                    'institute_name' => $data['institute_name'],
                    'passing_year' => $data['passing_year'],
                    'grade' => $data['grade'],
                ]);
        }
    }
    /*************** END Update Employee Education *************************/

    /*************** Update Employee Skills *************************/
    public static function updateSkills($id, $skills)
    {
        foreach ($skills as $data) {
            DB::table('proll_employee_skills')
                ->where(['employee_skill_id' => $data['employee_skill_id'], 'employee_id' => $id])
                ->update([
                    'skill_id' => $data['skill_id'],
                    'skill_level' => $data['skill_level'],
                ]);
        }
    }
    /*************** END Update Employee Skills *************************/

    /*************** Update Employee Languages *************************/
    public static function updateLanguages($id, $languages)
    {
        foreach ($languages as $data) {
            DB::table('proll_employee_language')
                ->where(['id' => $data['employee_language_id'], 'emp_id' => $id])
                ->update([
                    'language_id' => $data['language_id'],
                    'reading_level' => $data['reading_level'],
                    'writing_level' => $data['writing_level'],
                    'speaking_level' => $data['speaking_level'],
                ]);
        }
    }
    /*************** END Update Employee Languages *************************/

    /***********EMPLOYEE WORK EXPERIENCE UPDATION API************/
    public static function updateWorkExperience($id, $experience)
    {
        foreach ($experience as $data) {

            DB::table('proll_employee_job_history')
                ->where(['id' => $data['experienc_id'], 'empid' => $id])
                ->update([
                    'designation' => $data['designation'],
                    'organization' => $data['organization'],
                    'employer_address' => $data['employer_address'],
                    'employer_contact_number' => $data['employer_contact_number'],
                    'job_from' => $data['job_from'],
                    'job_to' => $data['job_to'],
                    'salary' => $data['salary'],
                    'reason_for_leave' => $data['reason_for_leave'],
                ]);
        }
    }
    /***********END OF EMPLOYEE WORK EXPERIENCE UPDATION API***********/

    /***********EMPLOYEE REFRENCES UPDATION API***********/
    public static function updateReferences($id, $references)
    {
        foreach ($references as $data) {
            DB::table('proll_employee_reference')
                ->where(['reference_id' => $data['reference_id'], 'empid' => $id])
                ->update([
                    'reference_person_name' => $data['reference_person_name'],
                    'relation' => $data['relation'],
                    'organization' => $data['organization'],
                    'designation' => $data['designation'],
                    'contact_no' => $data['contact_no'],
                    'address' => $data['address'],
                    'email' => $data['email'],
                    'known_since' => $data['known_since'],
                ]);
        }
    }
    /***********END OF EMPLOYEE REFRENCES UPDATION API***********/

    /***********EMPLOYEE BANK DETAILS UPDATION API***********/
    public static function updateBankDetails($id, $data)
    {
        DB::table('bank_accounts')
            ->where(['bank_account_id' => $data['bank_detail_id'], 'employee_id' => $id])
            ->update([
                'bank_branch_id' => $data['bank_branch_id'],
                'account_title' => $data['account_title'],
                'account_number' => $data['account_number'],
                'routing_code' => $data['routing_code'],
                'iban' => $data['iban'],
                'currency_id' => $data['currency_id'],
            ]);

    }
    /***********END OF EMPLOYEE BANK DETAILS UPDATION API***********/

    /***********EMPLOYEE Nominies UPDATION API***********/
    public static function updateNominiesDetail($id, $nominies)
    {
        foreach ($nominies as $data) {
            DB::table('proll_employee_dependents')
                ->where(['employee_id' => $id, 'dependent_id' => $data['nominee_id']])->update(
                [
                    'dependent_name' => $data['name'],
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'relationship' => $data['relationship'],
                    'cninc_number' => $data['cninc_number'],
                    'next_of_kins' => $data['next_of_kins'],
                ]
            );
        }

    }
    /***********END OF EMPLOYEE Nominies UPDATION API***********/

    /***********EMPLOYEE EMPLOYMENT UPDATION API***********/
    public static function updateEmploymentDetails($id, $data)
    {
        DB::table('proll_employee')
            ->where('id', $id)->update(
            [
                'empcode' => $data['employee_code'],
                'dept_id' => $data['department_id'],
                'designation' => $data['designation_id'],
                'reporting_to_id' => $data['external_lm_one_id'],
                'second_reporting_to_id' => $data['external_lm_two_id'],
                'loginname' => $data['official_email'],
                'cell_number' => $data['official_mobile_no'],
                'default_shift_id' => $data['default_shift_id'],
                'applyed_roster' => $data['roster_applicable'],
                'cid' => $data['company_id'],
                'loc_id' => $data['branch_id'],
                'doj' => $data['date_of_joining'],
                'status' => $data['employee_status'],
                'contract_start_date' => $data['contract_start_date'],
                'contract_end_date' => $data['contract_end_date'],
            ]
        );
        DB::table('proll_employee_detail')
            ->where('empid', $id)->update(
            [
                'empcode' => $data['employee_code'],
                'employeeno' => $data['people_code'],
            ]
        );

    }
    /***********END OF EMPLOYEE EMPLOYMENT UPDATION API***********/

    public static function updateEmployeeSalary($id, $data)
    {

        DB::table('proll_hr_salaries')
            ->where(['emp_id' => $id])->update(
            [
                'gross_monthly_salary' => $data['gross_salary'],
            ]
        );
    }

    public static function getAppNotificationCountByModule($cid, $module, $role, $user_id)
    {
        $ids = MultiApprovalHelpers::get_in_my_queue_applications($cid, $module, $role, $user_id, $view_status = 0);
        $app_count = ($ids ? count(explode(',', $ids)) : 0);
        for ($i = 2; $i <= 3; $i++) {
            // $app_count = $app_count + MultiApprovalHelpers::get_app_count_by_status($cid,$module,$role,$user_id,$status=$i);
        }
        return $app_count;
    }

    public static function geEmployeeYear($empid)
    {
        $doj = Employee::where(['id' => $empid])->pluck('doj')->first();
        if ($doj) {
            if (date('Y') == date('Y', strtotime($doj))) {
                $start_date = $doj;
                $end_date = date('Y-m-d', strtotime($doj . ' +1 year'));
            } else {
                $start_date = date('Y-') . date('m-d', strtotime($doj));
                if ($start_date < date('Y-m-d')) {
                    $end_date = date('Y-m-d', strtotime($start_date . ' +1 year'));
                } else {

                    $end_date = $start_date;
                    $start_date = date('Y-m-d', strtotime($start_date . ' -1 year'));
                }

            }
            return array('start_date' => $start_date, 'end_date' => $end_date);
        } else {
            return false;
        }

    }

//credientials headers
    public static $mis_headers = ["empcode"];

//relation with designation
    public function emp_designation()
    {
        return $this->hasone('App\Models\Designation', 'designation_id', 'designation');
    }
    public function country_details()
    {
        return $this->hasone('App\Models\country', 'country_id', 'country')->select('country_id', 'country');
    }
    public function emp_location()
    {
        return $this->hasone('App\Models\proll_client_location', 'loc_id', 'loc_id')->select('loc_id', 'loc_desc', 'address', 'landline');
    }

    //relation with designation
    public function department()
    {
        return $this->hasone('App\Models\Department', 'id', 'department_id')->select('id', 'department_name');
    }
    //relation with designation
    public function band()
    {
        return $this->hasone('App\Models\Band', 'id', 'emp_band')->select('id', 'unified_band');
    }
    //relation with proll reference data
    public function proll_reference_data()
    {
        return $this->hasone('App\Models\proll_reference_data', 'reference_key', 'job_status');
    }
    //relation with proll reference data
    public function proll_reference_data_jobstatus()
    {
        return $this->hasone('App\Models\proll_reference_data', 'reference_key', 'job_status')->wherehas('proll_reference_data_code', function ($q) {

            $q->where('reference_code', '=', 'Job_Status');

        });
    }
    //relation with proll reference data
    public function proll_reference_data_contract()
    {
        return $this->hasone('App\Models\proll_reference_data', 'id', 'contract_status');
    }
    public function salary_change_allownce()
    {
        return $this->belongsTo('App\Models\salary_allownce_bridge');
    }

//relation with event group model
    public function eventgroups()
    {
        return $this->hasMany(EventGroup::class, 'id');
    }
    public function employee_History()
    {
        return $this->belongsTo('App\Models\EmployeeChangeHistory', 'id', 'emp_id');
    }
    public function lm()
    {
        return $this->belongsTo('App\Models\DepartmentManager', 'id', 'empid');
    }

    public function assists()
    {
        return $this->hasMany('App\Models\proll_client_assist', 'id', 'emp_id');
    }

    public function awards()
    {
        return $this->hasMany('App\Models\Award', 'emp_id', 'id');
    }
}
