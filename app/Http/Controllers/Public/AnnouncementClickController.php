<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\VisitModels\ViewRecorder;
use App\Services\VisitModels\VisitorPseudonym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Preklik z oznamu alebo baneru: započíta klik a presmeruje na cieľ.
 *
 * Odkaz na webe vedie sem (Announcement::clickUrl()), nie priamo na cieľ, aby
 * sa preklik zarátal aj bez JavaScriptu a aj pri otvorení v novej karte.
 *
 * Počíta sa jeden klik na návštevníka a deň — dedup cez tabuľku `views`
 * s vlastným viewable_type, rovnako ako zobrazenia príspevkov (ViewRecorder).
 * Boty a prechody, ktoré neprišli z tohto webu, sa nerátajú, ale presmerujú
 * sa rovnako. Zlyhanie počítadla nikdy nezastaví presmerovanie.
 */
class AnnouncementClickController extends Controller
{
    public const VIEWABLE_TYPE = 'announcement_click';

    public function __invoke(Request $request, Announcement $announcement): RedirectResponse
    {
        // Vypnutý alebo odkazu zbavený oznam nesmie robiť z webu otvorený
        // presmerovač — cieľ berieme len z databázy a len keď oznam beží.
        if (! $announcement->link_url || ! $announcement->isRunning()) {
            return redirect('/');
        }

        if ($this->isCountable($request)) {
            try {
                $inserted = DB::table('views')->insertOrIgnore([
                    'viewable_type' => self::VIEWABLE_TYPE,
                    'viewable_id'   => $announcement->getKey(),
                    'visitor_hash'  => VisitorPseudonym::forRequest($request),
                    'viewed_on'     => now()->toDateString(),
                    'created_at'    => now(),
                ]);

                if ($inserted > 0) {
                    // Cez query builder: nehýbe updated_at oznamu.
                    DB::table('announcements')->where('id', $announcement->getKey())->increment('clicks');
                }
            } catch (\Throwable) {
                // Štatistika nesmie zhodiť preklik.
            }
        }

        return redirect()->away($announcement->link_url);
    }

    private function isCountable(Request $request): bool
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '' || preg_match(ViewRecorder::BOT_PATTERN, $userAgent) === 1) {
            return false;
        }

        // Klik na odkaz na vlastnej stránke posiela Sec-Fetch-Site:
        // same-origin. Adresa vložená zvonka (alebo prefetch) sa nerátá.
        // Starší prehliadač bez hlavičky prejde.
        $fetchSite = $request->header('Sec-Fetch-Site');

        return $fetchSite === null || $fetchSite === 'same-origin';
    }
}
