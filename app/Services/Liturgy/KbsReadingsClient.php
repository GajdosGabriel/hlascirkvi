<?php

namespace App\Services\Liturgy;

use App\Services\SystemLog\Recorder;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sťahovanie stránok liturgického kalendára KBS (lc.kbs.sk).
 *
 * KBS nemá API ani export, iba HTML — to rozoberá KbsReadingsParser.
 * Výpadok nie je chyba webu: vráti sa null a volajúci ukáže to, čo vie
 * vypočítať sám.
 */
class KbsReadingsClient
{
    /** Stránka jedného dňa — hlavička, citácie aj plné znenie. */
    public function day(CarbonInterface $date, ?int $timeout = null): ?string
    {
        return $this->get(['den' => $date->format('Y-m-d')], $timeout);
    }

    /** Mesačný prehľad — len hlavičky dní (názov, obdobie, cyklus, citácie). */
    public function month(int $year, int $month): ?string
    {
        return $this->get(['mesiac' => sprintf('%04d%02d01', $year, $month)]);
    }

    /** Adresa dňa pre návštevníka — odkaz na plné znenie. */
    public function dayUrl(CarbonInterface $date): string
    {
        return $this->baseUrl().'?den='.$date->format('Y-m-d');
    }

    /** @param array<string, string> $query */
    protected function get(array $query, ?int $timeout = null): ?string
    {
        try {
            $response = Http::timeout($timeout ?? (int) config('liturgy.timeout', 10))
                ->withHeaders(['User-Agent' => 'hlascirkvi.sk (liturgicke citania)'])
                ->get($this->baseUrl(), $query);

            if (! $response->successful() || trim($response->body()) === '') {
                Log::warning('Liturgický kalendár KBS vrátil '.$response->status(), $query);
                $this->recordUnavailable('HTTP '.$response->status(), $query);

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::warning('Liturgický kalendár KBS nedostupný: '.$e->getMessage(), $query);
            $this->recordUnavailable($e->getMessage(), $query);

            return null;
        }
    }

    /** Nočné sťahovanie ide po dňoch — do denníka stačí jeden záznam za hodinu. */
    protected function recordUnavailable(string $reason, array $query): void
    {
        if (Recorder::onceIn(60, 'liturgy-kbs-down')) {
            Recorder::warning('liturgy', 'unavailable', 'Liturgický kalendár KBS nedostupný',
                status: 'failed',
                context: ['error' => $reason, 'query' => $query],
            );
        }
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('liturgy.source_url'), '/').'/';
    }
}
