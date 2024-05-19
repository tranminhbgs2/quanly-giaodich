<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->integer('app_group_id')->comment('Id nhóm app');
            $table->string('name', 50)->comment('Tên app, hiển thị ở trang chủ');
            $table->string('code', 50)->comment('Mã app');
            $table->string('description', 255)->nullable()->comment('Mô tả thông tin app');
            $table->integer('sort')->default(1)->comment('Độ ưu tiên sắp xếp');
            $table->string('icon', 255)->comment('Đường dẫn icon của app');
            $table->boolean('is_public')->default(1)->comment('0-Chưa phát triển, chưa hoạt động, 1-Đang hoạt động');
            $table->string('scope')->default('ALL')->comment('Phạm vi hiển thị ứng dụng, ALL toàn quốc, khác ALL thì kiểm tra trong app_school');
            $table->tinyInteger('status')->default(1)->comment('Trạng thái: 0-Chờ duyệt, 1-Hoạt động, hiển thị, 2-Tạm khóa, ko hiển thị');
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
        Schema::dropIfExists('apps');
    }
}
