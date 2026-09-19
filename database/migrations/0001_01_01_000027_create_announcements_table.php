<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements')) {
            return;
        }

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            // App\Enums\AnnouncementPlacement.
            $table->string('placement', 32);
            // App\Enums\AnnouncementVariant.
            $table->string('variant', 32)->default('info');
            $table->string('title', 191);
            $table->text('body')->nullable();
            $table->string('link_url', 191)->nullable();
            $table->string('link_text', 60)->nullable();
            $table->boolean('dismissible')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('published_from')->nullable();
            $table->dateTime('published_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['placement', 'active'], 'announcements_placement_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
