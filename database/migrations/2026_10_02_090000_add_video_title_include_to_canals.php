<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            // Jedno slovo či fráza na riadok; import prijme len videá, ktorých
            // titulok niektorú obsahuje. Prázdne = bez obmedzenia.
            $table->text('video_title_include')->nullable();
        });

        // Predtým pevné pravidlo v kóde (VideoUploadFilter) pre Kresťanské spoločenstvo.
        DB::table('canals')->where('id', 256)->update(['video_title_include' => 'Bohoslužba Banská Bystrica']);
    }

    public function down(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->dropColumn('video_title_include');
        });
    }
};
