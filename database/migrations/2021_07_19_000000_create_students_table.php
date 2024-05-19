<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->integer('school_id')->nullable()->comment('Mã trường');
            $table->integer('class_id')->nullable()->comment('Mã lớp');
            $table->integer('parent_id')->nullable()->comment('Họ và tên cha');

            $table->string('fullname', 50)->comment('Họ và tên học sinh');
            $table->dateTime('birthday')->nullable()->comment('Ngày sinh');
            $table->string('gender', 1)->default('P')->comment('M: Nam, F: Nữ, P: Bí mật');
            $table->string('address', 255)->nullable()->comment('Địa chỉ');
            $table->string('email')->unique()->nullable()->comment('Email của học sinh nếu có');
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
        Schema::dropIfExists('students');
    }
}
