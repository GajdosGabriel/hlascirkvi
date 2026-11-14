<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Obrázkový baner v ozname. Keď má oznam obrázok, na webe sa namiesto textu
 * na farebnom pozadí vykreslí obrázok (aj animovaný GIF/WebP) s odkazom.
 * Voliteľný druhý obrázok sa ukáže na úzkych obrazovkách — široký baner
 * 728×90 by sa na mobile zmenšil na nečitateľný prúžok.
 *
 * Rozmery sa ukladajú kvôli atribútom width/height, aby stránka pri načítaní
 * obrázka neposkočila.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('image_path', 191)->nullable()->after('link_text');
            $table->unsignedSmallInteger('image_width')->nullable()->after('image_path');
            $table->unsignedSmallInteger('image_height')->nullable()->after('image_width');
            $table->string('mobile_image_path', 191)->nullable()->after('image_height');
            $table->unsignedSmallInteger('mobile_image_width')->nullable()->after('mobile_image_path');
            $table->unsignedSmallInteger('mobile_image_height')->nullable()->after('mobile_image_width');
            // Počet preklikov cez /oznam/{id}/klik (App\Http\Controllers\AnnouncementClickController).
            $table->unsignedInteger('clicks')->default(0)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'image_path', 'image_width', 'image_height',
                'mobile_image_path', 'mobile_image_width', 'mobile_image_height',
                'clicks',
            ]);
        });
    }
};
