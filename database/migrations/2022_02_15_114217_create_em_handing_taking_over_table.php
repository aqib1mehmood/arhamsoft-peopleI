<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmHandingTakingOverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('em_handing_taking_over', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('emp_id')->nullable();
            $table->foreign('emp_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('resignation_id')->nullable();
            $table->foreign('resignation_id')->references('id')->on('em_resignations')->onDelete('cascade');
            $table->unsignedInteger('taking_over_id')->nullable();
            $table->foreign('taking_over_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->string('name',100)->nullable();
            $table->boolean('handing_over_status')->default(0);
            $table->boolean('acceptance_status')->default(0);
            $table->tinyInteger('type')->comment('1 for responsibility and 2 for assets')->nullable();
            $table->longText('detail')->nullable();
            $table->date('handing_over_date');
            $table->date('taking_over_date');
            $table->longText('remarks')->nullable();
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
        Schema::dropIfExists('em_handing_taking_over');
    }
}
