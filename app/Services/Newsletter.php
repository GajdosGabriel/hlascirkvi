<?php


namespace App\Services;


use App\Mail\PostNewsletter;
use App\Models\Prayer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentUserRepository;

class Newsletter
{

    public function mountlyNewsletter()
    {
        // Predtým `->get()` nad všetkými odberateľmi naraz. Po dávkach sa
        // nedrží v pamäti celý zoznam.
        (new EloquentUserRepository)->usersEmailable()
            ->chunkById(200, fn ($users) => $this->handle($users));
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
                Mail::to($user)->queue(new PostNewsletter($content['posts'], $content['prayers']));
            } catch (\Throwable $e) {
                // Jeden neplatný e-mail zhodil celý beh a zvyšok odberateľov
                // newsletter nedostal.
                Log::warning('Newsletter sa nepodarilo zaradiť: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    protected function content(): array
    {
        return [
            'posts' => (new EloquentPostRepository)->newlleterMostVisited()->take(5)->get(),
            'prayers' => Prayer::latest()->take(5)->get(),
        ];
    }
}
