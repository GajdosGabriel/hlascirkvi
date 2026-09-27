<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canal_enrichments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('canal_id')->unique();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('retry_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('changes')->nullable();
            $table->json('evidence')->nullable();
            $table->json('recipients')->nullable();
            $table->json('notified')->nullable();
            $table->timestamp('notification_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canal_enrichments');
    }
};
