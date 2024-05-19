<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('name', 150)->comment('Tên hóa đơn thanh toán');
            $table->date('term')->comment('Kỳ thanh toán theo tháng');
            $table->integer('amount')->default(0)->comment('Số tiền cần thanh toán');
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
        Schema::dropIfExists('bills');
    }
}
