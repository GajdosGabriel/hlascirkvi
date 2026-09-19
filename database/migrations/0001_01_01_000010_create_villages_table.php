<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('villages')) {
            return;
        }

        Schema::create('villages', function (Blueprint $table) {
            // Číselník obcí SR, prevzatý aj s MyISAM a utf8. Primárny kľúč nemá.
            $table->engine('MyISAM');
            $table->charset('utf8');
            $table->collation('utf8_unicode_ci');

            $table->integer('id')->comment('Unique identifier');
            $table->string('fullname')->comment('Fullname');
            $table->string('shortname')->comment('Shortname');
            $table->string('zip', 6)->comment('ZIP');
            $table->integer('district_id')->comment('ID of district where villages belongs to');
            $table->integer('region_id')->comment('ID of region where villages belongs to');
            $table->boolean('use')->default(true)->comment('1 = use the row, 0 = not');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
