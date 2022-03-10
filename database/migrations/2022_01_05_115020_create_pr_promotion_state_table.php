<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrPromotionStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pr_promotion_state', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->unsignedInteger('promotion_id')->nullable();
            $table->foreign('promotion_id')->references('id')->on('pr_promotions')->onDelete('cascade');
            $table->unsignedInteger('approver_empid')->nullable();
            $table->foreign('approver_empid')->references('id')->on('proll_employee')->onDelete('cascade');
            $table->Integer('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->boolean('status')->default(0);
            $table->longText('comments')->nullable();
            $table->timestamp('date');
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
        Schema::dropIfExists('pr_promotion_state');
    }
}
