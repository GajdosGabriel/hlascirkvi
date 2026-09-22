<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 13.09.2018
 * Time: 7:52
 */

namespace App\Filters;

class PostFilters extends Filters
{
    protected $filters = [
        'mostVisited',
        'recomended',
        'first',
        'latestComments',
        'trends',
        'search',
        'unpublished',
        'deletedAt',
        'videoAvailable',
    ];

    public function recomended()
    {
        return $this->builder->has('favorites');
    }

    public function first()
    {
        return $this->builder->orderBy('id', 'asc');
    }

    public function mostVisited()
    {
        // id ako druhý stĺpec drží stránkovanie stabilné pri rovnakom počte
        // zobrazení a zároveň kopíruje poradie v posts_feed_views_index,
        // takže sa radí priamo z indexu.
        return $this->builder->orderBy('count_view', 'desc')->orderBy('id', 'desc');
    }

    public function latestComments()
    {
        return $this->builder->has('comments');
    }

    public function unpublished()
    {
        // Predtým `whereNull('published')`. Ten stĺpec vypĺňa import každému
        // príspevku hneď pri stiahnutí, takže filter ukazoval niečo iné než
        // buffer, ktorý sa pýtal na chýbajúci updater.
        return $this->builder->unpublished();
    }

    public function videoAvailable($value)
    {
        return $this->builder->where('video_available', 0);
    }

    public function deletedAt()
    {
        return $this->builder->onlyTrashed();
    }

    public function search()
    {
        session()->flash('search', $this->request->search);

        return $this->builder->where('title', 'LIKE', $this->likePattern($this->request->search));
    }

    /**
     * Najsledovanejšie z príspevkov zverejnených za posledné dva týždne.
     *
     * Pôvodná verzia pri každom otvorení zoskupila všetky denné zobrazenia
     * z tabuľky `views` a výsledok pripojila k celému výpisu príspevkov. Na
     * produkčných dátach preto kliknutie na Trend pôsobilo ako zamrznutá
     * stránka. Časové okno nad `published_at` je malé a indexované; samotné
     * poradie potom číta už uložený celkový počet zobrazení.
     */
    public function trends()
    {
        return $this->builder
            ->where('published_at', '>=', now()->subDays(14))
            ->orderBy('count_view', 'desc')
            ->orderBy('id', 'desc');
    }
}
