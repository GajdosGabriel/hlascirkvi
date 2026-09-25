<?php

use App\Services\Youtube\AuthorName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jednorazová oprava mien autorov z YouTube uložených ako handle
     * („PatriciaButelova" → „Patricia Butelova"). Nové importy už meno
     * upravujú cez App\Services\Youtube\AuthorName.
     */
    public function up(): void
    {
        DB::table('comments')
            ->whereNotNull('youtube_comment_id')
            ->whereNotNull('user_name')
            ->select('id', 'user_name')
            ->orderBy('id')
            ->chunkById(1000, function ($rows) {
                foreach ($rows as $row) {
                    $name = AuthorName::fromHandle($row->user_name);

                    if ($name !== null && $name !== $row->user_name) {
                        DB::table('comments')->where('id', $row->id)->update(['user_name' => $name]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Pôvodné handle sa neuchovávajú.
    }
};
