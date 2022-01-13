<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('organization_id')->unsigned();
            $table->string('title',  255);
            $table->string('slug',  255);
            $table->text('body')->nullable();
            $table->boolean('blocked')->default(0);
            $table->boolean('youtube_blocked')->default(0);
            $table->boolean('video_available')->nullable();
            $table->string('video_id')->nullable();
            $table->string('video_duration')->nullable();
            $table->integer('count_view')->defauld(0);
            $table->dateTime('deleted_at');
            $table->dateTime('published')->nullable();
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
        Schema::dropIfExists('post');
    }
}
