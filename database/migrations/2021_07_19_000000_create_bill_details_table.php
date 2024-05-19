<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_details', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('bill_id')->comment('Id hóa đơn');
            $table->string('name')->comment('Nội dung thanh toán');
            $table->float('quantity', 12,2)->default(0)->comment('Số lượng');
            $table->float('price', 12,2)->default(0)->comment('Đơn giá');
            $table->float('amount', 12,2)->default(0)->comment('Thành tiền');
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
        Schema::dropIfExists('bill_details');
    }
}
