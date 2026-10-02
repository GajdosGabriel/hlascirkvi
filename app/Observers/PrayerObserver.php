<?php

namespace App\Observers;

use App\Models\Prayer;
use App\Services\Prayers\SpamDetector;
use App\Services\SystemLog\Recorder;

class PrayerObserver
{
    /**
     * Reklamný spam sa maže už pri vzniku (formulár, import zo zdrojov),
     * nie až dodatočným `prayer:despam`. Soft delete, dá sa obnoviť.
     */
    public function created(Prayer $prayer)
    {
        $hit = app(SpamDetector::class)->inspect($prayer->title, (string) $prayer->body);

        if ($hit) {
            $prayer->delete();

            Recorder::info('moderation', 'prayer_spam', 'Prosba označená ako spam: ' . $hit['reason'], subject: $prayer, context: $hit);
        }
    }
}
