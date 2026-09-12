<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oznamy, ktoré superadmin vypisuje na web bez zásahu do šablón. Zapnutie
 * (`active`) je zámerne oddelené od časového okna — text sa dá pripraviť
 * dopredu, vypnúť po akcii a znovu použiť o rok, bez mazania záznamu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            // Kam na webe oznam patrí — hodnoty drží App\Enums\AnnouncementPlacement.
            $table->string('placement', 32);
            // Farebné ladenie pruhu — App\Enums\AnnouncementVariant.
            $table->string('variant', 32)->default('info');
            $table->string('title', 191);
            $table->text('body')->nullable();
            // Voliteľné tlačidlo oznamu. Odkaz bez popisu nesie názov oznamu.
            $table->string('link_url', 191)->nullable();
            $table->string('link_text', 60)->nullable();
            // Návštevník si oznam môže zavrieť; zapamätá sa mu v prehliadači.
            $table->boolean('dismissible')->default(false);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('published_from')->nullable();
            $table->dateTime('published_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Výpis sa vždy pýta na jedno umiestnenie a len na zapnuté oznamy.
            $table->index(['placement', 'active'], 'announcements_placement_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
