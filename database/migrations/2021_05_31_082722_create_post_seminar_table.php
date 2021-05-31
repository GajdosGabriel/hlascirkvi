<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostSeminarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_seminar', function (Blueprint $table) {
            $table->integer('seminar_id')->unsigned()->index();
            $table->integer('post_id')->unsigned()->index();

            $table->foreign('seminar_id')->references('id')->on('seminars');
            $table->foreign('post_id')->references('id')->on('posts');

            $table->unique(['post_id', 'seminar_id']);
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
        Schema::dropIfExists('post_seminar');
    }
}
