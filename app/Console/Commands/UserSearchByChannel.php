<?php

namespace App\Console\Commands;

use App\Services\VideoUpload;
use Illuminate\Console\Command;

class UserSearchByChannel extends Command
{
    protected $signature = 'UserSearchByChannelAndPlaylist
        {--canal= : Spracovať len kanál s týmto ID (ručné overenie)}';

    protected $description = 'Stiahne nové videá z kanálov a playlistov YouTube';

    public function handle(): int
    {
        $canal = $this->option('canal');

        $saved = (new VideoUpload)->handle($canal !== null ? (int) $canal : null);

        $this->info('Nových videí: ' . $saved);

        return self::SUCCESS;
    }
}
