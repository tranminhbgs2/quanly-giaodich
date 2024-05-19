<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('account_type')->default('CUSTOMER')->comment('Loại tài khoản: CUSTOMER/SYSTEM/STUDENT/TEACHER/PARENT');
            $table->string('username', 50)->unique()->comment('Tên tài khoản');
            $table->string('sscid', 50)->unique()->comment('Mã học sinh từ hệ thống SSC, không có thì lấy bằng username');
            $table->string('fullname', 50)->comment('Họ và tên học sinh');
            $table->string('birthday', 50)->nullable()->comment('Ngày sinh');
            $table->string('address', 255)->nullable()->comment('Địa chỉ');
            $table->string('email')->unique()->nullable()->comment('Email của học sinh nếu có');
            $table->string('phone', 15)->nullable()->comment('Số điện thoại');
            $table->string('display_name')->nullable()->comment('Tên tài khoản');

            //$table->integer('school_id')->nullable()->comment('Mã trường');
            //$table->string('school_code', 25)->nullable()->comment('Mã trường');
            //$table->string('school_name', 25)->nullable()->comment('Tên trường');

            //$table->integer('class_id')->nullable()->comment('Mã lớp');
            //$table->string('class_code', 50)->nullable()->comment('Mã lớp');
            //$table->string('class_name', 50)->nullable()->comment('Tên lớp');

            //$table->integer('parent_id')->nullable()->comment('Họ và tên cha');
            //$table->string('father_name', 50)->nullable()->comment('Họ và tên cha');
            //$table->string('mother_name', 50)->nullable()->comment('Họ và tên mẹ');

            //$table->string('ssc_request_id', 25)->comment('Số điện thoại');
            //$table->string('request_id', 25)->comment('Id request lúc gửi lên');

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->text('notes')->nullable()->comment('Ghi chú về tài khoản này nếu có');
            $table->tinyInteger('status')->default(1)->comment('Trạng thái: 0 (Tài khoản chưa được kích hoạt), 1 (Tài khoản đang hoạt động), 2 (Tài khoản đang tạm khóa), 3 (Tài khoản đã chuyển trường), 4 (Tài khoản đã bị khóa)');

            $table->dateTime('last_login')->nullable()->comment('Đăng nhập lần cuối');
            $table->dateTime('last_logout')->nullable()->comment('Đăng xuất lần cuối');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
