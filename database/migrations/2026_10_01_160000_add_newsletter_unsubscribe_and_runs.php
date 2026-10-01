<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dôkaz o odvolaní súhlasu (GDPR): kedy a z akej adresy sa odber zrušil.
        // Denník udalostí sa maže po mesiaci, preto to nesie samotný účet.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('newsletter_unsubscribed_at')->nullable()->after('send_email');
            $table->string('newsletter_unsubscribed_ip', 45)->nullable()->after('newsletter_unsubscribed_at');
        });

        // Jeden riadok na mesiac. Unikátny kľúč je poistka proti dvojitému
        // rozoslaniu (plánovač + ručné spustenie).
        Schema::create('newsletter_runs', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique();
            $table->unsignedInteger('queued')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_runs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['newsletter_unsubscribed_at', 'newsletter_unsubscribed_ip']);
        });
    }
};
