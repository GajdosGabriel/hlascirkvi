<?php


namespace App\Services;


use App\Mail\PostNewsletter;
use App\Models\Prayer;
use App\Services\SystemLog\Recorder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentUserRepository;

class Newsletter
{
    /** Súhrn behu pre denník udalostí. */
    protected int $queued = 0;

    protected int $skipped = 0;

    public function mountlyNewsletter()
    {
        $this->queued = $this->skipped = 0;

        // Predtým `->get()` nad všetkými odberateľmi naraz. Po dávkach sa
        // nedrží v pamäti celý zoznam.
        (new EloquentUserRepository)->usersEmailable()
            ->chunkById(200, fn ($users) => $this->handle($users));

        // Jednotlivé maily zapíše denník sám (mail.sent / mail.failed), keď
        // ich fronta odošle; tu je len to, koľko sa ich do fronty dostalo.
        Recorder::record('newsletter', 'queued', sprintf('Newsletter zaradený: %d, preskočený: %d', $this->queued, $this->skipped),
            level: $this->skipped > 0 ? 'warning' : 'info',
            status: $this->queued > 0 ? 'ok' : 'skipped',
            context: ['queued' => $this->queued, 'skipped' => $this->skipped],
        );
    }

    public function handle($users)
    {
        // Obsah je pre všetkých rovnaký, takže sa zostaví raz. PostNewsletter
        // si ho doťahoval v build(), teda jedným dopytom na každého príjemcu.
        $content = $this->content();

        foreach ($users as $user) {
            try {
                // queue() namiesto send(): rozposielanie nedrží beh príkazu
                // a zlyhaný e-mail sa dá zopakovať z tabuľky failed_jobs.
                Mail::to($user)->queue(new PostNewsletter($content['posts'], $content['prayers'], $user));
                $this->queued++;
            } catch (\Throwable $e) {
                // Jeden neplatný e-mail zhodil celý beh a zvyšok odberateľov
                // newsletter nedostal.
                $this->skipped++;

                Log::warning('Newsletter sa nepodarilo zaradiť: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                ]);

                Recorder::warning('newsletter', 'skipped', 'Newsletter sa nepodarilo zaradiť',
                    status: 'skipped',
                    recipient: $user->email,
                    userId: $user->id,
                    context: Recorder::exception($e),
                );
            }
        }
    }

    protected function content(): array
    {
        return [
            'posts' => (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get(),
            'prayers' => Prayer::published()->latest()->take(5)->get(),
        ];
    }
}
