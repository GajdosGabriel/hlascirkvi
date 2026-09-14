<?php

namespace App\Console\Commands;

use App\Services\Liturgy\KbsReadingsClient;
use App\Services\Liturgy\KbsReadingsParser;
use App\Services\Liturgy\LiturgicalCalendar;
use Illuminate\Console\Command;

/**
 * Porovná vypočítaný liturgický kalendár s kalendárom KBS za celý rok.
 *
 * Z mesačných prehľadov KBS (12 požiadaviek) berie obdobie, liturgický rok
 * a cyklus každého dňa a pri fériách a nedeliach bez sviatku aj názov dňa.
 * Každý rozdiel vypíše — výpočet sa tak dá overiť bez ručného listovania.
 */
class LiturgyVerify extends Command
{
    protected $signature = 'liturgia:overit {rok? : Kalendárny rok (predvolene aktuálny)}';

    protected $description = 'Porovná vypočítaný liturgický kalendár s kalendárom KBS';

    public function handle(KbsReadingsClient $client, KbsReadingsParser $parser, LiturgicalCalendar $calendar): int
    {
        $year = (int) ($this->argument('rok') ?: now()->year);
        $pause = max(0, (int) config('liturgy.pause_ms', 1000));

        $checked = 0;
        $problems = [];

        for ($month = 1; $month <= 12; $month++) {
            if ($month > 1 && $pause > 0) {
                usleep($pause * 1000);
            }

            $html = $client->month($year, $month);

            if ($html === null) {
                $problems[] = [sprintf('%04d-%02d', $year, $month), 'mesiac sa nepodarilo stiahnuť'];

                continue;
            }

            foreach ($parser->parseMonth($html) as $ymd => $kbs) {
                if (! str_starts_with($ymd, sprintf('%04d-%02d-', $year, $month))) {
                    continue;
                }

                $day = $calendar->for($ymd);
                $checked++;
                $diffs = [];

                if ($kbs['season_label'] !== null && $kbs['season_label'] !== $day->season->label()) {
                    $diffs[] = "obdobie: KBS „{$kbs['season_label']}“, výpočet „{$day->season->label()}“";
                }

                if ($kbs['liturgical_year'] !== null && $kbs['liturgical_year'] !== $day->liturgicalYear) {
                    $diffs[] = "liturgický rok: KBS {$kbs['liturgical_year']}, výpočet {$day->liturgicalYear}";
                }

                if ($kbs['cycle'] !== null && $kbs['cycle'] !== $day->cycleLabel()) {
                    $diffs[] = "cyklus: KBS {$kbs['cycle']}, výpočet {$day->cycleLabel()}";
                }

                // „1. adventná nedeľa [rok B, cyklus I.]" — cyklus sa kontroluje zvlášť.
                $title = preg_replace('/\s*\[[^\]]*\]$/u', '', $kbs['title']);

                if ($kbs['feria'] && $title !== $day->title) {
                    $diffs[] = "názov: KBS „{$title}“, výpočet „{$day->title}“";
                }

                if ($diffs !== []) {
                    $problems[] = [$ymd, implode('; ', $diffs)];
                }
            }
        }

        // Nula skontrolovaných dní znamená, že KBS zmenila stránku — nie zhodu.
        if ($checked === 0) {
            $this->error('Z KBS sa nepodarilo prečítať ani jeden deň.');

            return self::FAILURE;
        }

        if ($problems !== []) {
            $this->table(['Deň', 'Rozdiel'], $problems);
            $this->error(sprintf('Skontrolovaných %d dní, rozdielov: %d.', $checked, count($problems)));

            return self::FAILURE;
        }

        $this->info(sprintf('Skontrolovaných %d dní roku %d, výpočet sa s KBS zhoduje.', $checked, $year));

        return self::SUCCESS;
    }
}
