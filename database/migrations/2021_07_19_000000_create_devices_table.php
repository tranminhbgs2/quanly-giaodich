<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->bigInteger('user_id')->comment('Id của học sinh/giáo viên');
            $table->string('channel', 25)->default('FIREBASE')->comment('Kênh bắn');
            $table->string('token', 255)->comment('Id của thiết bị');
            $table->string('platform', 25)->nullable()->comment('Tên nền tảng: iOS/Android');
            $table->boolean('is_active')->default(1)->comment('0-Khóa, 1-Hoạt động');

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
        Schema::dropIfExists('devices');
    }
}
