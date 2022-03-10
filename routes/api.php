<?php

use App\Helpers\RouteHelper;
use Illuminate\Support\Facades\Route;

$route_helper = new RouteHelper();

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', 'EmployeeController@login');
Route::post('/logout', 'EmployeeController@logout');

// Route::middleware('auth:api')->group( function () {

// });

Route::post('getaccountapproval', [MultiApprovalController::class, 'GetApplicationApprovelDetail']);

Route::group(['middleware' => ['auth:api', 'cors']], function () use ($route_helper) {
    //Awards Api's

    Route::get('awards', 'Api\awards\AwardController@index');
    Route::post('award-upload-csv', 'Api\awards\AwardController@uploadCSV');
    Route::post('awards-store', 'Api\awards\AwardController@store');
    Route::get('award-types', 'Api\awards\AwardTypeController@index');
    Route::get('employee-for-reward', 'Api\awards\ProllEmployeeController@get_employee_for_award');
    Route::get('employee-award', 'Api\awards\AwardController@get_award_via_employee');
    Route::get('recent-award', 'Api\awards\AwardController@get_recent_awards');
    Route::get('show-award', 'Api\awards\AwardController@show_award');
    Route::get('award-status', 'Api\awards\AwardController@award_application_status');
    Route::get('award-attachments', 'Api\awards\AwardController@show_award_attachments');
    Route::post('awards-resubmit', 'Api\awards\AwardController@resubmit_award');
    Route::get('award-employees', 'Api\awards\ProllEmployeeController@award_employees');
    Route::post('change-award-status', 'Api\awards\AwardController@change_award_status');
    Route::get('withdraw-award', 'Api\awards\AwardController@destroy');
    Route::get('has-issue-letter', 'Api\awards\AwardController@has_issue_letter');
    Route::post('change-multiple-award_status', 'Api\awards\AwardController@change_multiple_award_status');
    Route::get('ceo-award-list', 'Api\awards\AwardController@ceo_award_list');

    //Promotion Api's

    Route::get('promotions', 'Api\promotion\PromotionController@index');
    Route::post('promotion-store', 'Api\promotion\PromotionController@store');
    Route::get('promotion-types', 'Api\promotion\PromotionTypeController@index');
    Route::get('appraisal-types', 'Api\promotion\AppraisalTypeController@index');
    Route::get('promotion-reason', 'Api\promotion\PromotionReasonController@index');
    Route::get('departments', 'Api\promotion\DepartmentController@index');
    Route::get('designations', 'Api\promotion\DesignationController@index');
    Route::get('bands', 'Api\promotion\EmployeeBandController@index');
    Route::get('get-same-dept-employees', 'Api\awards\ProllEmployeeController@get_same_dept_employees');
    Route::get('get-all-employees', 'Api\awards\ProllEmployeeController@get_all_employees');
    Route::get('location', 'Api\promotion\LocationController@index');
    Route::get('employee-for-promotion', 'Api\awards\ProllEmployeeController@get_employee_for_promotion');
    Route::get('employee-promotion', 'Api\promotion\PromotionController@get_promotion_via_employee');
    Route::get('recent-promotion', 'Api\promotion\PromotionController@get_recent_promotion');
    Route::get('show-promotion', 'Api\promotion\PromotionController@show_promotion');
    Route::get('promotion-attachments', 'Api\promotion\PromotionController@show_promotion_attachments');
    Route::post('promotion-resubmit', 'Api\promotion\PromotionController@resubmit_promotion');
    Route::get('promotion-filter', 'Api\promotion\PromotionController@promotion_filter');
    Route::get('promotion-employees', 'Api\awards\ProllEmployeeController@promotion_employees');
    Route::post('change-promotion-status', 'Api\promotion\PromotionController@change_promotion_status');
    Route::get('withdraw-promotion', 'Api\promotion\PromotionController@destroy');
    Route::get('ceo-promotion-list', 'Api\promotion\PromotionController@ceo_promotion_list');

    Route::group(['prefix' => 'admin'], function () use ($route_helper) {

        $route_helper->callRoute('GeoTracker');
        $route_helper->callRoute('LoanAdvance');
        $route_helper->callRoute('PerformanceManagement');
        $route_helper->callRoute('Leave');
    });

    //Exit management apis
    Route::get('getResignation', 'Api\Exit_management\ExitManagementController@index');
    //get employee resignation application
    Route::get('get-employee-resignation-application', 'Api\Exit_management\ExitManagementController@get_employee_resignation_application');
    //get employee resignation application by resignation id
    Route::get('get-resignation-application', 'Api\Exit_management\ExitManagementController@get_resignation_application');
    //Save Resign Application
    Route::post('save-Resignation-application', 'Api\Exit_management\ExitManagementController@save_resignation_application');
    //change the Resignation application status
    Route::post('change-Resignation-status', 'Api\Exit_management\ExitManagementController@change_resigntion_application_status');
    //get questions

    Route::get('get-questions', 'Api\Exit_management\QuestionController@get_questions');
    //save question types
    Route::post('save-question-type', 'Api\Exit_management\QuestionController@save_question_types');
    //save question
    Route::get('get_drop_down_value', 'Api\Exit_management\ExitManagementController@get_drop_down_value');
    //save employee assets
    Route::post('save-employee-assets', 'Api\Exit_management\EmployeeResourceController@save_employee_assets');
    //save employee responsibility
    Route::post('save-employee-responsibility', 'Api\Exit_management\EmployeeResourceController@save_employee_responsibility');
    //update employee assets
    Route::post('update-employee-assets', 'Api\Exit_management\EmployeeResourceController@update_employee_assets');
    //save employee responsibility
    Route::post('update-employee-responsibility', 'Api\Exit_management\EmployeeResourceController@update_employee_responsibility');
    //get assets
    Route::get('get-assets', 'Api\Exit_management\EmployeeResourceController@get_assets');
    //get responsibility
    Route::get('get-responsibility', 'Api\Exit_management\EmployeeResourceController@get_responsibility');
    //get employee assets
    Route::get('get-employee-assets', 'Api\Exit_management\EmployeeResourceController@get_employee_assets');
    //get employee responsibility
    Route::get('get-employee-responsibility', 'Api\Exit_management\EmployeeResourceController@get_employee_responsibility');
    //get ecf approvel queue
    Route::post('save-ecf-approvel', 'Api\Exit_management\ExitManagementController@ecf_approval_queue');
    //update approve status of assets by admin
    Route::post('update-assets-status', 'Api\Exit_management\EmployeeResourceController@update_assets_status_by_admin');
    //submit interview questions
    Route::post('save-exit-interview', 'Api\Exit_management\QuestionController@save_interview_questions');
    //submit final settlement
    Route::post('save-final-settlement', 'Api\Exit_management\FinalSettlementController@final_settlement');
    //get employee salary details
    Route::get('get-employee-salary', 'Api\Exit_management\FinalSettlementController@get_employee_salary');
    //get fianace settlement of employee
    Route::get('get-employee-dues', 'Api\Exit_management\FinalSettlementController@get_employee_dues');
    //@shahbaz
    //save finance clearance
    Route::post('save-finance-clearance', 'Api\Exit_management\ExitManagementController@save_finance_clearance');
    //Get Exit Interview
    Route::get('get-exit-interview', 'Api\Exit_management\QuestionController@get_exit_interview');
    //Get ECF launch resignation
    Route::get('get-ecf-launch-resignation', 'Api\Exit_management\ExitManagementController@get_ecf_launch_resignation');
    //Get ECF Resignation Application
    Route::get('get-ecf-resignation-application', 'Api\Exit_management\ExitManagementController@get_ecf_resignation_application');
    //Get applications which are ecf launched by hr
    Route::get('get-ecf-approved-application', 'Api\Exit_management\ExitManagementController@get_ecf_approved_resignations');
    //Delete Asset
    Route::delete('delete-asset', 'Api\Exit_management\EmployeeResourceController@delete_asset');
    //Delete responsibility
    Route::delete('delete-responsibility', 'Api\Exit_management\EmployeeResourceController@delete_responsibility');
});
