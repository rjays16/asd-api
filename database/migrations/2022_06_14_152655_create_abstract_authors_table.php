<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbstractAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abstract_authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('abstract_id');
            $table->foreign('abstract_id')->references('id')->on('abstracts');
            $table->string('last_name', 255)->nullable();
            $table->string('first_name', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('email', 191)->nullable();
            $table->string('institution', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->string('affiliation_city', 255)->nullable();
            $table->string('affiliation_country', 255)->nullable();
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
        Schema::dropIfExists('abstract_authors');
    }
}
