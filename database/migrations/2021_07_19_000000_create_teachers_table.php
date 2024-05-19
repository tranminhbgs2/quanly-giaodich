<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->string('fullname', 50)->comment('Họ và tên');
            $table->string('birthday', 50)->nullable()->comment('Ngày sinh');
            $table->string('gender', 1)->default('P')->comment('M: Nam, F: Nữ, P: Bí mật');
            $table->string('address', 255)->nullable()->comment('Địa chỉ');
            $table->string('email')->unique()->nullable()->comment('Email');
            $table->string('phone', 15)->nullable()->comment('Số điện thoại');

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
        Schema::dropIfExists('teachers');
    }
}
