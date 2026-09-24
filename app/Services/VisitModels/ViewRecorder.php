<?php

namespace App\Services\VisitModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Započíta zobrazenie príspevku. Nahradilo balík cyrildewit/eloquent-viewable
 * aj pôvodný Counter, ktorý zvyšoval count_view pri každom načítaní stránky.
 *
 * Návštevníka rozpoznáva pseudonymom, nie cookie — viď VisitorPseudonym.
 *
 * Volá ho beacon z prehliadača (PostController::view, route post.view), nie
 * render stránky. Do 19. 9. 2026 sa zapisovalo pri GET detailu a crawler
 * s rotujúcimi IP a user-agentmi tak za 9 dní pridal ~3 mil. „návštevníkov".
 *
 * Nikdy nevyhadzuje výnimku: zlyhanie štatistiky nesmie zhodiť zobrazenie
 * stránky.
 */
class ViewRecorder
{
    /**
     * Naivné boty. Presnú detekciu tu nerobíme — cieľom je, aby počítadlo
     * nemeralo crawlerov, nie neprestrelná ochrana. (Balík na to ťahal
     * jaybizzle/crawler-detect; kvôli jednému regexu to nestálo za závislosť.)
     */
    private const BOT_PATTERN = '~bot|crawl|spider|slurp|headless|preview|monitor|curl|wget|python-requests|facebookexternalhit|whatsapp|telegram~i';

    /**
     * @return bool  true, keď zobrazenie naozaj pribudlo
     */
    public function record(Model $model, Request $request): bool
    {
        try {
            if (! $model->getKey() || ! $this->isCountable($model, $request)) {
                return false;
            }

            $column = method_exists($model, 'viewsCountColumn')
                ? $model->viewsCountColumn()
                : 'count_view';

            // insertOrIgnore v spojení s unikátnym indexom views_unique_per_day
            // rieši dedup na deň: vráti 0, keď riadok už existuje, takže druhé
            // zobrazenie toho istého návštevníka v ten istý deň počítadlo
            // nezvýši.
            $inserted = DB::table('views')->insertOrIgnore([
                'viewable_type' => $model->getMorphClass(),
                'viewable_id' => $model->getKey(),
                'visitor_hash' => VisitorPseudonym::forRequest($request),
                'viewed_on' => now()->toDateString(),
                'created_at' => now(),
            ]);

            if ($inserted === 0) {
                return false;
            }

            // increment() na query builderi, nie na modeli — nesmie hýbať
            // updated_at ani spúšťať observery.
            DB::table($model->getTable())
                ->where($model->getKeyName(), $model->getKey())
                ->increment($column);

            $model->setAttribute($column, (int) $model->getAttribute($column) + 1);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function isCountable(Model $model, Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '' || preg_match(self::BOT_PATTERN, $userAgent) === 1) {
            return false;
        }

        // Beacon posiela axios, ktorý nastavuje X-Requested-With
        // (resources/js/bootstrap.js). Ručne poskladaný POST ho väčšinou nemá.
        if (! $request->ajax()) {
            return false;
        }

        // Moderné prehliadače pripájajú Sec-Fetch-Site; keď ho request má,
        // musí prísť z vlastnej stránky. Starší prehliadač bez hlavičky prejde.
        $fetchSite = $request->header('Sec-Fetch-Site');

        if ($fetchSite !== null && $fetchSite !== 'same-origin') {
            return false;
        }

        // Kanál si vlastné čísla nenafukuje — rovnaké pravidlo ako mal pôvodný
        // Counter::userIdentity().
        $user = auth()->user();

        if ($user !== null && $user->canal_id !== null && $user->canal_id == $model->canal_id) {
            return false;
        }

        return true;
    }
}
