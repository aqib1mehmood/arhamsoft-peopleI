<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicDropdown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_dropdown_types', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('proll_client')->onDelete('cascade');
            $table->string('display_text')->nullable();
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
        Schema::dropIfExists('dynamic_dropdown');
    }
}
