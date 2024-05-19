<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->string('father_fullname', 50)->nullable()->comment('Họ và tên cha');
            $table->string('father_birthday', 50)->nullable()->comment('Ngày sinh');
            $table->string('father_address', 255)->nullable()->comment('Địa chỉ');
            $table->string('father_email')->unique()->nullable()->comment('Email');
            $table->string('father_phone', 15)->nullable()->comment('Số điện thoại');

            $table->string('mother_fullname', 50)->nullable()->comment('Họ và tên mẹ');
            $table->string('mother_birthday', 50)->nullable()->comment('Ngày sinh');
            $table->string('mother_address', 255)->nullable()->comment('Địa chỉ');
            $table->string('mother_email')->unique()->nullable()->comment('Email');
            $table->string('mother_phone', 15)->nullable()->comment('Số điện thoại');

            $table->string('other_fullname', 50)->nullable()->comment('Họ và tên khác nếu có');
            $table->string('other_birthday', 50)->nullable()->comment('Ngày sinh');
            $table->string('other_address', 255)->nullable()->comment('Địa chỉ');
            $table->string('other_email')->unique()->nullable()->comment('Email');
            $table->string('other_phone', 15)->nullable()->comment('Số điện thoại');

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
        Schema::dropIfExists('parents');
    }
}
