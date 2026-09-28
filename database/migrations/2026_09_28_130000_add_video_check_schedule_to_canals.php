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
            $table->dateTime('video_check_next_at')->nullable()->index();
            $table->dateTime('video_check_attempted_at')->nullable();
            $table->dateTime('video_check_succeeded_at')->nullable();
            $table->string('video_check_error')->nullable();
            $table->dateTime('name_search_window_start')->nullable();
            $table->dateTime('name_search_window_end')->nullable();
            $table->dateTime('name_search_completed_until')->nullable();
            $table->text('name_search_page_token')->nullable();
        });

        // Existujúce dni rozložia prvý beh; nespustiť všetko naraz po nasadení.
        $today = now()->startOfDay();
        foreach (range(0, 6) as $day) {
            $date = $today->copy()->addDays(($day - $today->dayOfWeek + 7) % 7);
            DB::table('canals')->where('import_day', $day)->update([
                'video_check_next_at' => $date->copy()->setTime(6, 55),
            ]);
            DB::table('canals')->where('import_day', $day)
                ->where(fn ($q) => $q->where('youtube_channel', '<>', '')->orWhere('youtube_playlist', '<>', ''))
                ->update(['video_check_next_at' => $date->copy()->setTime(16, 24)]);
        }
    }

    public function down(): void
    {
        Schema::table('canals', function (Blueprint $table) {
            $table->dropIndex(['video_check_next_at']);
            $table->dropColumn([
                'video_check_next_at', 'video_check_attempted_at', 'video_check_succeeded_at', 'video_check_error',
                'name_search_window_start', 'name_search_window_end', 'name_search_completed_until', 'name_search_page_token',
            ]);
        });
    }
};
