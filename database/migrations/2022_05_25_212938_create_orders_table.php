<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('convention_member_id');
            $table->foreign('convention_member_id')->references('id')->on('convention_members');
            $table->decimal('amount')->default(0);
            $table->decimal('intl_amount')->default(0);
            $table->decimal('convenience_fee')->default(0);
            $table->unsignedBigInteger('status');
            $table->foreign('status')->references('id')->on('order_status');
            $table->unsignedBigInteger('fee_id');
            $table->foreign('fee_id')->references('id')->on('fees');
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
        Schema::dropIfExists('orders');
    }
}
