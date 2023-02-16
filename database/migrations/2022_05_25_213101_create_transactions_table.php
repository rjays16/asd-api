<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders');
            $table->decimal('amount')->default(0);
            $table->decimal('intl_amount')->default(0);
            $table->unsignedBigInteger('ideapay_id')->nullable();
            $table->foreign('ideapay_id')->references('id')->on('ideapay');
            $table->decimal('ideapay_fee')->default(0);
            $table->decimal('intl_ideapay_fee')->default(0);
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
        Schema::dropIfExists('transactions');
    }
}
