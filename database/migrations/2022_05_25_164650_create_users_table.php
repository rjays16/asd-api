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
            $table->id();
            $table->string('first_name', 191);
            $table->string('middle_name')->nullable();
            $table->string('last_name', 191)->nullable();
            $table->string('suffix', 191)->nullable();
            $table->string('prof_suffix', 191)->nullable();
            $table->string('certificate_name')->nullable();

            $table->string('email', 191)->unique()->nullable();
            $table->string('password', 150);

            $table->string('country', 100)->nullable();

            $table->unsignedBigInteger('role');
            $table->foreign('role')->references('id')->on('roles');

            $table->unsignedBigInteger('status')->nullable();
            $table->foreign('status')->references('id')->on('user_status');

            $table->rememberToken();
            $table->text('active_token')->nullable();

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
