<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('buffer_publications')) {
            return;
        }

        Schema::create('buffer_publications', function (Blueprint $table) {
            // Denník buffer publishera — čo v ktorý deň pustil buffer.
            $table->id();
            $table->unsignedInteger('post_id')->unique();
            $table->unsignedInteger('canal_id')->index();
            // Naplánovaný čas slotu; podľa neho sa počíta, koľko dnes vyšlo.
            $table->dateTime('slot_at')->index();
            // Príspevok zo starého frontu (archív), nie čerstvý import.
            $table->boolean('archive')->default(false);
            // Kedy sa video naimportovalo — zverejnenie prepíše posts.created_at.
            $table->dateTime('arrived_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buffer_publications');
    }
};
