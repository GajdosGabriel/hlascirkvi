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

        // Len zverejnené — video čakajúce v bufferi sa inak objavilo na
        // kanáli skôr ako na titulke.
        $posts = $canal->posts()
            ->published()
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
