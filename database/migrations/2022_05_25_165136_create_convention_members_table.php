<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConventionMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('convention_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('scope')->default(false); # If the scope is true, it is International. Else, local
            $table->boolean('is_pds')->default(false);
            $table->string('prc_number', 255)->nullable();
            $table->string('pds_number', 255)->nullable();
            $table->string('resident_certificate', 500)->nullable();
            $table->string('institution_organization', 500)->nullable();
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
        Schema::dropIfExists('convention_members');
    }
}
