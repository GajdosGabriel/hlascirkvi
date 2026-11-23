<?php

namespace App\Http\Controllers\Public;

use App\Models\Canal;
use App\Filters\PostFilters;
use App\Repositories\Contracts\PostRepository;
use App\Http\Controllers\Controller;

class CanalController extends Controller
{
    protected $posts;

    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
    }

    public function show(Canal $canal, PostFilters $filters)
    {
        // Výber z archívu: mesiac sám o sebe nič neznamená, berie sa až
        // s rokom — tak, ako ich navigátor v bočnom paneli aj skladá.
        // Hodnoty mimo rozsahu (roboty skúšajú ?mesiac=1024458429) sa
        // ignorujú, ako keby v adrese neboli.
        $year  = (int) request('rok');
        $year  = $year >= 1900 && $year <= 2100 ? $year : null;
        $month = (int) request('mesiac');
        $month = $year && $month >= 1 && $month <= 12 ? $month : null;

        // Osobný kanál kedysi dopĺňalo hľadanie podľa mena (zrušené 11/2026,
        // ťahalo aj cudzie videá). Namiesto neho ukáže popri vlastných aj
        // videá z iných kanálov, v ktorých osobu rozpoznalo
        // App\Services\Persons\PersonDetector — s YouTube zdrojom aj bez neho.
        $personId = \App\Models\Person::where('canal_id', $canal->id)->value('id');

        // Len zverejnené — video čakajúce v bufferi sa inak objavilo na
        // kanáli skôr ako na titulke.
        $posts = \App\Models\Post::query()
            ->where(fn ($query) => $query->where('posts.canal_id', $canal->id)
                ->when($personId, fn ($q) => $q->orWhereIn('posts.id',
                    fn ($ids) => $ids->select('post_id')->from('person_post')->where('person_id', $personId))))
            ->when($personId, fn ($query) => $query->whereHas('canal'))
            ->published()->available()
            ->when($year, fn ($query) => $query->whereYear('created_at', $year))
            ->when($month, fn ($query) => $query->whereMonth('created_at', $month))
            // Rovnaké prepínače ako na úvodnej stránke (odporúčané,
            // najsledovanejšie, hľadanie), len zúžené na tento kanál.
            ->filter($filters)
            ->paginate(24)
            ->withQueryString();

        return view('canals.index', [
            'canal'         => $canal,
            'posts'         => $posts,
            'collections'   => $canal->seminars()->published()->orderBy('title')
                ->withCount(['posts' => fn ($q) => $q->published()->available()])
                ->paginate(12, ['*'], 'collections_page')->withQueryString(),
            // Kto v kanáli pôsobí: vlastník osobného kanála a osoby, ktoré
            // sa vo videách kanála objavujú najčastejšie.
            'persons'       => \App\Models\Person::query()
                ->where(fn ($q) => $q->where('persons.canal_id', $canal->id)
                    ->orWhereIn('persons.id', fn ($ids) => $ids->select('person_id')->from('canal_person')->where('canal_id', $canal->id)))
                ->leftJoin('canal_person', fn ($join) => $join->on('canal_person.person_id', '=', 'persons.id')
                    ->where('canal_person.canal_id', $canal->id))
                ->orderByRaw('persons.canal_id = ? desc', [$canal->id])
                ->orderByDesc('canal_person.posts_count')
                ->limit(12)
                ->get(['persons.*']),
            'year'          => $year,
            'month'         => $month,
        ] + $this->channelPanels($canal));
    }

    /**
     * Panely okolo výpisu kanála. Sú to krátke dopyty nad indexmi kanála,
     * preto stoja pri sebe — pohľad ich len vykreslí.
     */
    protected function channelPanels(Canal $canal)
    {
        return [
            'summary'       => $this->posts->canalSummary($canal->id),
            'archive'       => $this->posts->canalArchive($canal->id),
            'topPosts'      => $this->posts->mostViewedInCanal($canal->id, null, 5),
            'comments'      => $this->posts->latestCommentsInCanal($canal->id, 6),
            'commentsCount' => $this->posts->countCommentsInCanal($canal->id),
        ];
    }
}
