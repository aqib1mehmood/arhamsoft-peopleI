<?php

namespace App\Http\Controllers\api\awards;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Promotion;
use App\Models\Award;
use Validator;


class ProllEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $employees = Employee::where('cid', $request->client_id)->get();
        $count = $employees->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Employees Found' : 'No Employees Found!',
            'results' => $count > 0 ? $employees : null,
        ]);
    }

    public function award_employees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $employees = Award::leftjoin('proll_employee as emp', 'emp.id', 'pr_awards.emp_id')
        ->groupBy('emp.id','emp.name')
        ->select('emp.id as employee_id','emp.name as employee_name')
        ->get();
        $count = $employees->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Employees Found' : 'No Employees Found!',
            'results' => $count > 0 ? $employees : null,
        ]);
    }



    public function promotion_employees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $employees = Promotion::leftjoin('proll_employee as emp', 'emp.id', 'pr_promotions.emp_id')
        ->groupBy('emp.id','emp.name')
        ->select('emp.id as employee_id','emp.name as employee_name')
        ->get();
        $count = $employees->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Employees Found' : 'No Employees Found!',
            'results' => $count > 0 ? $employees : null,
        ]);
    }

    public function get_employee_for_award(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer',
            'designation_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $employees = Employee::where('department_id', $request->department_id)->where('designation', $request->designation_id)->get();
        $count = $employees->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Employee Found' : 'No Employee Found!',
            'results' => $count > 0 ? $employees : null,
        ]);
    }

    public function get_employee_for_promotion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer',
            'designation_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }
        $employees = Employee::join('employee_bands as band', 'band.id', 'proll_employee.emp_band')
            ->where('department_id', $request->department_id)->where('designation', $request->designation_id)
            ->select('proll_employee.id as employee_id', 'proll_employee.name as employee_name', 'band.band_desc', 'band.band_description')
            ->get();
        $count = $employees->count();
        return response()->json([
            'success' => $count > 0 ? true : false,
            'message' => $count > 0 ? 'Employee Found' : 'No Employee Found!',
            'results' => $count > 0 ? $employees : null,
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
    public function destroy($id)
    {
        //
    }
}
