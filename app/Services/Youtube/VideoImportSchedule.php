<?php

namespace App\Services\Youtube;

use App\Models\Canal;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class VideoImportSchedule
{
    public static function hasSource(Canal $canal): bool
    {
        return filled($canal->youtube_channel) || filled($canal->youtube_playlist);
    }

    public static function assignDayForNewSource(Canal $canal): void
    {
        // Pridaný YouTube zdroj dostane týždenný termín aj bez výberu vo
        // formulári. Už nastavený deň ani existujúci denný import nemeníme.
        if (self::hasSource($canal)
            && $canal->import_day === null
            && (! $canal->exists || $canal->isDirty(['youtube_channel', 'youtube_playlist']))) {
            $canal->import_day = self::suggestedDay($canal->id);
        }
    }

    public static function nextDate(Canal $canal, ?CarbonInterface $after = null): ?Carbon
    {
        if ($canal->import_day === null && ! self::hasSource($canal)) {
            return null;
        }

        $after ??= now();
        $next = Carbon::instance($after)->setTime(...(self::hasSource($canal) ? [16, 24] : [6, 55]));
        if ($canal->import_day === null) {
            return $next->lte($after) ? $next->addDay() : $next;
        }

        $next->addDays(($canal->import_day - $next->dayOfWeek + 7) % 7);

        return $next->lte($after) ? $next->addWeek() : $next;
    }

    public static function suggestedDay(?int $exceptId = null): int
    {
        $counts = Canal::without('favorites')->whereNull('youtube_disabled_at')->notPaused()
            ->whereNotNull('import_day')
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->selectRaw('import_day, count(*) as total')->groupBy('import_day')
            ->pluck('total', 'import_day');

        return collect(array_keys(Canal::IMPORT_DAYS))
            ->sortBy(fn ($day) => [($counts[$day] ?? 0), ($day - now()->dayOfWeek + 7) % 7])
            ->first();
    }

    public static function due(Canal $canal): bool
    {
        if ($canal->import_day === null) {
            return self::hasSource($canal);
        }

        // Opakovaný denný príkaz nespotrebuje kvótu znova. Výpadok dobehneme zajtra.
        if ($canal->video_check_attempted_at?->isToday()) {
            return false;
        }

        return $canal->video_check_next_at
            ? $canal->video_check_next_at->lte(now())
            : $canal->import_day === now()->dayOfWeek;
    }

    public static function reset(Canal $canal): void
    {
        $canal->forceFill([
            'video_check_next_at' => self::nextDate($canal),
            'video_check_attempted_at' => null,
            'video_check_error' => null,
            'name_search_page_token' => null,
            'name_search_window_start' => null,
            'name_search_window_end' => null,
        ]);
    }

    public static function succeeded(Canal $canal): void
    {
        $canal->forceFill([
            'video_check_succeeded_at' => now(),
            'video_check_next_at' => self::nextDate($canal),
            'video_check_error' => null,
        ])->save();
    }

    public static function failed(Canal $canal, \Throwable $error): void
    {
        // Výnimka môže obsahovať URL s API kľúčom. Formulár dostane bezpečný text.
        $message = $error instanceof YoutubeApiException && $error->stopsRun()
            ? 'YouTube odmietol prístup alebo bola vyčerpaná kvóta. Kontrolu zopakujeme nasledujúci deň.'
            : 'Kontrola videí zlyhala. Zopakujeme ju nasledujúci deň.';
        $canal->forceFill(['video_check_error' => $message])->save();
    }
}
