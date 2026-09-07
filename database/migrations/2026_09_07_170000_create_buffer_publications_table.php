<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Denník buffer publishera. Sám updater 15 na to nestačí — ten istý updater
 * pripája aj import videí (App\Services\VideoUpload), takže z príspevkov sa
 * nedá vyčítať, čo v ktorý deň pustil buffer a čo pribudlo inak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buffer_publications', function (Blueprint $table) {
            $table->id();
            // posts.id aj organizations.id sú int unsigned (historická schéma),
            // preto rovnaký typ a nie bigInteger.
            $table->unsignedInteger('post_id')->unique();
            $table->unsignedInteger('organization_id')->index();
            // Naplánovaný čas slotu; podľa neho sa počíta, koľko dnes vyšlo.
            $table->dateTime('slot_at')->index();
            // Príspevok zo starého frontu (archív), nie čerstvý import.
            $table->boolean('archive')->default(false);
            // Kedy sa video naimportovalo. Zverejnenie prepíše `posts.created_at`
            // časom vydania, takže inde sa už pôvodný čas nedá zistiť — a bez
            // neho sa nedá spočítať, koľko toho do buffera denne pribúda.
            $table->dateTime('arrived_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buffer_publications');
    }
};
