<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigInteger('id', true);

            $table->integer('app_id')->comment('Id module/app cần thông báo');
            $table->bigInteger('school_id')->comment('Id trường cần thông báo');
            $table->string('name', 255)->comment('Tên thông báo');
            $table->text('summary')->comment('Mô tả ngắn gọn thông báo');
            $table->string('image')->comment('Ảnh đại diện của thông báo');
            $table->longText('content')->nullable()->comment('Nội dung chi tiết thông báo');
            $table->string('notes', 255)->nullable()->comment('Lưu ý thêm nếu có');

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
        Schema::dropIfExists('announcements');
    }
}
