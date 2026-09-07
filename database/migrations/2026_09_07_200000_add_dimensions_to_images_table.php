<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rozmery predlohy. Bez nich nemá <img> čo dať prehliadaču dopredu
     * a obsah pod obrázkom pri načítaní odskočí.
     *
     * Kontrola tabuľky je tu preto, že migrácia vytvárajúca images
     * v repozitári nie je — na prázdnej databáze nie je čo meniť.
     */
    public function up(): void
    {
        if (! Schema::hasTable('images')) {
            return;
        }

        Schema::table('images', function (Blueprint $table) {
            if (! Schema::hasColumn('images', 'width')) {
                $table->unsignedInteger('width')->nullable()->after('variants');
            }

            if (! Schema::hasColumn('images', 'height')) {
                $table->unsignedInteger('height')->nullable()->after('width');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('images')) {
            return;
        }

        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['width', 'height']);
        });
    }
};
