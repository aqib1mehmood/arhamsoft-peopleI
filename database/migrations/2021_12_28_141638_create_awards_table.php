<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAwardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr_awards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('emp_id')->nullable();
            $table->foreign('emp_id')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('award_type')->nullable();
            $table->foreign('award_type')->references('id')->on('pr_award_types')->onDelete('cascade');
            $table->double('amount')->nullable();
            $table->longText('brief_reason')->nullable();
            $table->date('fiscal_year')->nullable();
            $table->tinyInteger('year_type',2)->comment('0 for quarter 1 for fiscal')->nullable();
            $table->boolean('issue_letter')->default(0);
            $table->tinyInteger('award_status')->default(0);
            $table->unsignedInteger('deleted_by')->nullable();
            $table->foreign('deleted_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->unsignedInteger('updated_by')->nullable();
            $table->tinyInteger('action_by_role',4)->comment('2 for lm 3 for hr and 5 for ceo')->nullable();
            $table->tinyInteger('resubmit_status',4)->default(0);
            $table->string('created_by_role',10)->nullable();
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
        Schema::dropIfExists('awards');
    }
}
