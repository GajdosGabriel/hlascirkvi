<?php

namespace App\Console\Commands;

use App\Services\Youtube\CommentSync;
use Illuminate\Console\Command;

class YoutubeCommentsExtract extends Command
{
    protected $signature = 'youtube:comments
        {--limit=50 : Počet videí v jednom behu (najviac 50)}';

    protected $description = 'Stiahne komentáre z YouTube, doplní trvanie videí a označí nedostupné videá';

    public function handle(): int
    {
        $stats = (new CommentSync)->handle((int) $this->option('limit'));

        $this->info("Videí: {$stats['posts']}, nových komentárov: {$stats['comments']}");

        return self::SUCCESS;
    }
}
