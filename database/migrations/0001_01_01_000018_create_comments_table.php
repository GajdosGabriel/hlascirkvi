<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comments')) {
            return;
        }

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('commentable_id');
            $table->string('commentable_type', 191);
            $table->unsignedInteger('parent_id')->nullable();
            // Komentáre stiahnuté z YouTube (App\Services\Youtube\CommentSync).
            $table->string('youtube_comment_id', 100)->nullable()->unique();
            $table->unsignedInteger('user_id');
            $table->text('body');
            $table->boolean('published')->default(true);
            $table->enum('type', ['offer', 'look'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('user_avatar')->nullable();
            $table->string('user_name', 100)->nullable();

            $table->index(['deleted_at', 'created_at'], 'comments_created_index');
            $table->index(['commentable_type', 'commentable_id', 'deleted_at'], 'comments_commentable_index');
            $table->index(['parent_id', 'deleted_at'], 'comments_parent_index');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
