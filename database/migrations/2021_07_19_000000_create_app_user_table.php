<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_user', function (Blueprint $table) {
            $table->bigInteger('user_id')->comment('ID học sinh sử dụng app con');;
            $table->integer('app_id')->comment('ID của app');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_user');
    }
}
