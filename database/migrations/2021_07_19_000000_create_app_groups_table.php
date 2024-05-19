<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nhóm app');
            $table->string('code', 50)->nullable()->comment('Mã nhóm app');
            $table->string('icon', 255)->nullable()->comment('Đường dẫn icon của app');
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
        Schema::dropIfExists('app_groups');
    }
}
