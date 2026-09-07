<?php

namespace App\Console\Commands;

use App\Services\Buffer;
use Illuminate\Console\Command;


class BufferPublisher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PublisherBufferVideo
        {--force : Zverejní ďalší príspevok hneď, bez ohľadu na plán}
        {--plan : Iba vypíše dnešný plán, nič nezverejní}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vypúšťa príspevky z buffera po jednom počas dňa';

    /**
     * Execute the console command.
     */
    public function handle(Buffer $buffer): int
    {
        $status = $buffer->status();

        if ($this->option('plan')) {
            $this->renderStatus($status);

            return self::SUCCESS;
        }

        $post = $buffer->handler((bool) $this->option('force'));

        if ($post === null) {
            // Bežný stav — cron beží každých pár minút, zverejňuje sa zriedka.
            $this->line(sprintf(
                'Nič sa nezverejňuje. Čaká %d príspevkov, dnes vyšlo %d z %d, ďalší %s.',
                $status['waiting'],
                $status['done_today'],
                count($status['plan']),
                $status['next_slot']?->format('H:i') ?? '—'
            ));

            return self::SUCCESS;
        }

        $archive = \App\Models\BufferPublication::where('post_id', $post->id)->value('archive');

        $this->info(sprintf(
            'Zverejnené%s: #%d %s (%s) o %s',
            $archive ? ' z archívu' : '',
            $post->id,
            \Illuminate\Support\Str::limit($post->title, 60),
            $post->organization->title ?? $post->organization_id,
            $post->created_at->format('H:i:s')
        ));

        return self::SUCCESS;
    }

    protected function renderStatus(array $status): void
    {
        $this->line(sprintf(
            'V bufferi čaká %d príspevkov, prítok %.1f/deň, dnes ich má vyjsť %d (zatiaľ %d, z toho %d z archívu).',
            $status['waiting'],
            $status['inflow'],
            count($status['plan']),
            $status['done_today'],
            $status['archive_today']
        ));

        $rows = [];

        foreach ($status['plan'] as $index => $slot) {
            $rows[] = [
                $slot->format('H:i:s'),
                match (true) {
                    $index < $status['done_today'] => 'zverejnené',
                    $index === $status['done_today'] => '← najbližšie',
                    default => 'čaká',
                },
            ];
        }

        $this->table(['čas', 'stav'], $rows);
    }
}
