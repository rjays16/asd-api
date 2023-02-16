<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbstractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abstracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('convention_member_id');
            $table->foreign('convention_member_id')->references('id')->on('convention_members');
            $table->string('title');
            $table->text('structured_abstract');
            $table->string('keywords');
            $table->string('is_conflict_interest')->default(false);
            $table->string('conflict_interest')->nullable();
            $table->boolean('is_commercial')->default(false);
            $table->string('commercial_funding')->nullable();
            $table->date('submission_date')->nullable();
            $table->integer('abstract_type')->nullable();
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
        Schema::dropIfExists('abstracts');
    }
}
