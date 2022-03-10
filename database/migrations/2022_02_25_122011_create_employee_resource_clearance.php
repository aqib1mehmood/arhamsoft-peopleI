<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeResourceClearance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_resource_clearance', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('emp_id')->nullable();
            $table->foreign('emp_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('resignation_id')->nullable();
            $table->foreign('resignation_id')->references('id')->on('em_resignations')->onDelete('cascade');
            $table->unsignedInteger('employee_resource_id')->nullable();
            $table->foreign('employee_resource_id')->references('id')->on('employee_resource')->onDelete('cascade');
            $table->string('over_used_cost',100)->nullable();
            $table->boolean('acceptance_status')->default(0);
            $table->longText('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('proll_employee')->onDelete('cascade');
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
        Schema::dropIfExists('employee_resource_clearance');
    }
}
