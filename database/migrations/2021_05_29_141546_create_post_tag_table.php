<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned()->index();
            $table->integer('post_id')->unsigned()->index();

            $table->foreign('tag_id')->references('id')->on('tags');
            $table->foreign('post_id')->references('id')->on('posts');

            $table->unique(['post_id', 'tag_id']);
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
        Schema::dropIfExists('post_tag');
    }
}
