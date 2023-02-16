<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->year('year')->nullable();
            $table->boolean('is_pds')->default(false);
            $table->boolean('scope')->default(false); # if the scope is true, it is global (USD). Else, it is local (PHP)
            $table->decimal('amount')->default(0);
            $table->decimal('late_amount')->default(0);
            $table->date('late_amount_starts_on')->nullable();
            $table->boolean('uses_late_amount')->default(false);
            $table->integer('member_type')->default(1);
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
        Schema::dropIfExists('fees');
    }
}
