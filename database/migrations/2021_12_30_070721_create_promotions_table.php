<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr_promotions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('emp_id')->nullable();
            $table->foreign('emp_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('promotion_type')->nullable();
            $table->foreign('promotion_type')->references('id')->on('pr_promotion_types')->onDelete('cascade');
            $table->unsignedInteger('appraisal_type')->nullable();
            $table->foreign('appraisal_type')->references('id')->on('pr_appraisal_types')->onDelete('cascade');
            $table->unsignedInteger('promotion_reason')->nullable();
            $table->foreign('promotion_reason')->references('id')->on('pr_promotion_reasons')->onDelete('cascade');
            $table->Integer('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department_hierarchy')->onDelete('cascade');
            $table->Integer('designation_id')->nullable();
            $table->foreign('designation_id')->references('designation_id')->on('proll_client_designation')->onDelete('cascade');
            $table->Integer('band_id')->nullable();
            $table->foreign('band_id')->references('id')->on('employee_bands')->onDelete('cascade');
            $table->Integer('location_id')->nullable();
            $table->foreign('location_id')->references('loc_id')->on('proll_client_location')->onDelete('cascade');
            $table->double('amount')->nullable();
            $table->longText('brief_reason')->nullable();
            $table->boolean('promotion_status')->default(0);
            $table->unsignedInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->tinyInteger('action_by_role',4)->comment('2 for lm 3 for hr and 5 for ceo')->nullable();
            $table->tinyInteger('resubmit_status',4)->default(0);
            $table->string('created_by_role',10)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
