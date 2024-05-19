<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Tên slide');
            $table->string('image')->comment('Đường dẫn ảnh banner quảng cáo');
            $table->string('action_type')->comment('Loại hành động để điều hướng tương ứng');
            $table->integer('record_id')->nullable()->comment('Id của bản ghi tương ứng với link nếu có');
            $table->string('redirect_to', 255)->nullable()->comment('Link điều hướng nếu có');
            $table->tinyInteger('status')->default(1)->comment('Trạng thái: 0-Chưa kích hoạt, 1-Hoạt động, 2-Tạm dừng');
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
        Schema::dropIfExists('ads');
    }
}
