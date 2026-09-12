<?php

namespace App\Console\Commands;

use App\Models\Canal;
use App\Services\Youtube\ChannelId;
use App\Services\Youtube\PlaylistId;
use Illuminate\Console\Command;

/**
 * V `organizations.youtube_channel` sa našli adresy kanálov namiesto ID
 * (napr. https://www.youtube.com/@EVSchcemviac). YouTube na taký `channelId`
 * odpovie 403 „The request is not properly authorized", takže denný import
 * videí na tých kanáloch padal každý deň rovnako.
 *
 * Formulár už adresu prepíše na ID sám (App\Http\Requests\CanalRequest),
 * tento príkaz dorovná záznamy, ktoré v databáze zostali.
 */
class YoutubeFixChannelIds extends Command
{
    protected $signature = 'youtube:fix-channel-ids
        {--fix : Zapísať nájdené ID do databázy (bez prepínača len vypíše návrh)}';

    protected $description = 'Prepíše adresy kanálov a playlistov YouTube na ID';

    public function handle(): int
    {
        $canals = Canal::query()
            ->where(function ($query) {
                $query->where('youtube_channel', '<>', '')
                    ->orWhere('youtube_playlist', '<>', '');
            })
            ->orderBy('id')
            ->get(['id', 'title', 'youtube_channel', 'youtube_playlist']);

        $rows = [];
        $fixed = 0;
        $unresolved = 0;

        foreach ($canals as $canal) {
            $update = [];

            foreach (['youtube_channel', 'youtube_playlist'] as $field) {
                $value = trim((string) $canal->{$field});

                if ($value === '' || $this->isId($field, $value)) {
                    continue;
                }

                $id = $this->resolve($field, $value);

                $rows[] = [
                    $canal->id,
                    $canal->title,
                    $field === 'youtube_channel' ? 'kanál' : 'playlist',
                    $value,
                    $id ?? 'NEPODARILO SA',
                ];

                if ($id === null) {
                    $unresolved++;
                    continue;
                }

                $update[$field] = $id;
            }

            if ($update !== [] && $this->option('fix')) {
                // So správnym ID má import odkiaľ brať, takže sa zapína.
                $canal->update($update + [
                    'youtube_disabled_at' => null,
                    'youtube_disabled_reason' => null,
                ]);
                $fixed++;
            }
        }

        if ($rows === []) {
            $this->info('Všetky kanály aj playlisty majú ID, niet čo opravovať.');

            return self::SUCCESS;
        }

        $this->table(['id', 'kanál', 'pole', 'uložená hodnota', 'ID'], $rows);

        if ($this->option('fix')) {
            $this->info('Opravených kanálov: ' . $fixed);
        } else {
            $this->warn('Nič sa nezapisovalo. Na zápis spustite príkaz s --fix.');
        }

        if ($unresolved > 0) {
            $this->warn($unresolved . '× sa ID nepodarilo zistiť — tie treba doplniť ručne.');
        }

        return self::SUCCESS;
    }

    private function isId(string $field, string $value): bool
    {
        return $field === 'youtube_channel'
            ? ChannelId::isId($value)
            : PlaylistId::isId($value);
    }

    private function resolve(string $field, string $value): ?string
    {
        if ($field === 'youtube_playlist') {
            return PlaylistId::fromInput($value);
        }

        try {
            return ChannelId::resolve($value);
        } catch (\Throwable $e) {
            // Vyčerpaná kvóta alebo výpadok API nesmie zhodiť celý beh —
            // ostatné kanály sa majú prejsť aj tak.
            $this->warn('YouTube API: ' . $e->getMessage());

            return null;
        }
    }
}
