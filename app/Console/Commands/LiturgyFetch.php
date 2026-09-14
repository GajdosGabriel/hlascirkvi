<?php

namespace App\Console\Commands;

use App\Models\LiturgicalDay;
use App\Services\Liturgy\DailyReadings;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Stiahne liturgické čítania z kalendára KBS na dni dopredu.
 *
 * Beží denne, ale na KBS siaha len pri dňoch, ktoré v databáze chýbajú
 * alebo sú staršie ako liturgy.refresh_after_days — bežne teda jedna
 * požiadavka za deň. Medzi požiadavkami je prestávka (liturgy.pause_ms).
 */
class LiturgyFetch extends Command
{
    protected $signature = 'liturgia:stiahnut
        {--od= : Prvý deň vo formáte RRRR-MM-DD (predvolene dnes)}
        {--dni= : Koľko dní stiahnuť (predvolene liturgy.days_ahead)}
        {--znova : Stiahnuť aj dni, ktoré už v databáze sú}';

    protected $description = 'Stiahne liturgické čítania z kalendára KBS do tabuľky liturgical_days';

    public function handle(DailyReadings $readings): int
    {
        try {
            $from = $this->option('od')
                ? CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->option('od'))
                : CarbonImmutable::today();
        } catch (\Throwable) {
            $this->error('Neplatný dátum v --od, očakáva sa RRRR-MM-DD.');

            return self::FAILURE;
        }

        $days = max(1, (int) ($this->option('dni') ?? config('liturgy.days_ahead', 45)));
        $pause = max(0, (int) config('liturgy.pause_ms', 1000));
        $stale = now()->subDays((int) config('liturgy.refresh_after_days', 14))->format('Y-m-d H:i:s');

        $existing = LiturgicalDay::query()
            ->whereBetween('date', [$from->format('Y-m-d'), $from->addDays($days - 1)->format('Y-m-d')])
            ->toBase()
            ->pluck('fetched_at', 'date');

        $fetched = $skipped = $failed = 0;

        for ($i = 0; $i < $days; $i++) {
            $date = $from->addDays($i);
            $ymd = $date->format('Y-m-d');

            if (! $this->option('znova') && isset($existing[$ymd]) && $existing[$ymd] >= $stale) {
                $skipped++;

                continue;
            }

            if ($fetched + $failed > 0 && $pause > 0) {
                usleep($pause * 1000);
            }

            if ($record = $readings->refresh($date)) {
                $fetched++;
                $this->line("  {$ymd}  {$record->title}");
            } else {
                $failed++;
                $this->warn("  {$ymd}  nepodarilo sa stiahnuť");
            }
        }

        $this->info(sprintf(
            'liturgia:stiahnut: stiahnutých %d, preskočených %d, neúspešných %d',
            $fetched,
            $skipped,
            $failed
        ));

        return $failed > 0 && $fetched === 0 ? self::FAILURE : self::SUCCESS;
    }
}
