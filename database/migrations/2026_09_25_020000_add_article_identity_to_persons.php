<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('persons', 'first_name')) {
            Schema::table('persons', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('prefix');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('title_suffix')->nullable()->after('last_name');
            $table->string('normalized_name')->nullable()->after('name');
            $table->string('email')->nullable()->change();
            $table->index('normalized_name');
        });
        }

        Schema::create('person_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->unsignedInteger('post_id');
            $table->string('source', 20);
            $table->string('matched_text')->nullable();
            $table->unsignedTinyInteger('confidence')->default(100);
            $table->timestamps();
            $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            $table->unique(['person_id', 'post_id']);
            $table->index(['post_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_post');
        Schema::table('persons', function (Blueprint $table) {
            $table->dropIndex(['normalized_name']);
            $table->dropColumn(['first_name', 'middle_name', 'last_name', 'title_suffix', 'normalized_name']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
