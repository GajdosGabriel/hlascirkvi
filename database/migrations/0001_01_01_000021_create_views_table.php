<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('views')) {
            return;
        }

        Schema::create('views', function (Blueprint $table) {
            // Jeden riadok = jeden návštevník (denný pseudonym) a jeden deň.
            // Trvalé číslo žije v `posts.count_view`, staré riadky sa mažú
            // (app:views-prune).
            $table->bigIncrements('id');
            $table->string('viewable_type', 60);
            $table->unsignedInteger('viewable_id');
            $table->char('visitor_hash', 64);
            $table->date('viewed_on');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['viewable_type', 'viewable_id', 'visitor_hash', 'viewed_on'], 'views_unique_per_day');
            $table->index(['viewable_type', 'viewable_id', 'viewed_on'], 'views_target_day_index');
            $table->index('viewed_on', 'views_viewed_on_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
