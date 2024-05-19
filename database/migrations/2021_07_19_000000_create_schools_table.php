<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id()->comment('Id của trường học');

            $table->integer('province_id')->nullable()->comment('Id của tỉnh/tp');
            $table->integer('district_id')->nullable()->comment('Id của quận/huyện');
            $table->string('name', 150)->comment('Tên trường học');
            $table->string('code', 25)->comment('Mã trường học');
            $table->string('address', 255)->nullable()->comment('Địa chỉ trường học');
            $table->string('phone', 25)->nullable()->comment('Số điện thoại');
            $table->string('fax', 25)->nullable()->comment('Số fax');
            $table->string('email', 191)->unique()->comment('Email');
            $table->string('headmaster', 50)->comment('Họ và tên hiệu trưởng');

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
        Schema::dropIfExists('schools');
    }
}
