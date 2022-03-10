<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmHandingoverItStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('em_handingover_it_status', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('emp_id')->nullable();
            $table->foreign('emp_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('resignation_id')->nullable();
            $table->foreign('resignation_id')->references('id')->on('em_resignations')->onDelete('cascade');
            $table->unsignedInteger('asset_id')->nullable();
            $table->foreign('asset_id')->references('id')->on('em_handing_taking_over')->onDelete('cascade');
            $table->string('condition',100)->nullable();
            $table->string('final_settlement_cost',100)->nullable();
            $table->longText('remarks')->nullable();
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
        Schema::dropIfExists('em_handingover_it_status');
    }
}
