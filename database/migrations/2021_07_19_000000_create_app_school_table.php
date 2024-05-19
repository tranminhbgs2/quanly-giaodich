<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_school', function (Blueprint $table) {
            $table->integer('school_id')->comment('ID trường áp dụng app');
            $table->integer('app_id')->comment('ID của app');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_school');
    }
}
