<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Overené cez YouTube videos.list 4. 10. 2026. Ide o dva odlišné
        // záznamy, jeden mal po premenovaní prenosu zastarané metadáta.
        $title = '27.9.2026 - Marek Jurčo - Cirkev ako dar';
        DB::table('posts')->where('video_id', 'bK0bgT-DIds')
            ->where('title', '20.9.2026 - Laci Mižík - Božia výzbroj')->whereNull('deleted_at')
            ->update([
                'title' => $title, 'slug' => Str::slug($title),
                'video_duration' => 'PT1H26M26S', 'youtube_published_at' => \Carbon\Carbon::parse('2026-09-27T21:34:07Z')->setTimezone(config('app.timezone')),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Návrat k starému názvu by znovu vytvoril falošné podozrenie na duplicitu.
    }
};
