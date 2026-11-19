<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Person;

/**
 * Stránka osoby: videá, v ktorých vystupuje, jej vlastný kanál a zbory či
 * spoločenstvá, v ktorých pôsobí.
 */
class PersonController extends Controller
{
    public function show(Person $person)
    {
        $posts = $person->posts()
            ->with('canal')
            ->published()->available()
            ->whereHas('canal')
            ->orderByDesc('posts.published_at')->orderByDesc('posts.id')
            ->paginate(24);

        // Strana za koncom vráti poslednú platnú, ako výpis na titulke.
        if ($posts->currentPage() > 1 && $posts->isEmpty()) {
            return redirect()->route('person.show', [$person, 'page' => $posts->lastPage() > 1 ? $posts->lastPage() : null]);
        }

        // Kanály, v ktorých osoba pôsobí, okrem jej vlastného (ten má vlastný
        // odkaz). Poradie podľa počtu videí osoby v kanáli.
        $canals = $person->canals()
            ->whereNull('canals.deleted_at')
            ->when($person->canal_id, fn ($query) => $query->where('canals.id', '<>', $person->canal_id))
            ->orderByDesc('canal_person.posts_count')
            ->get();

        return view('persons.show', [
            'person' => $person,
            'posts' => $posts,
            'ownCanal' => $person->canal()->whereNull('deleted_at')->first(),
            'canals' => $canals,
        ]);
    }
}
