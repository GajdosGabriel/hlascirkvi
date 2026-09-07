<?php

namespace App\Http\Controllers\Public;

use App\Models\Organization;
use App\Filters\PostFilters;
use App\Repositories\Contracts\PostRepository;
use App\Http\Controllers\Controller;

class OrganizationController extends Controller
{
    protected $posts;

    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
    }

    public function show(Organization $organization, PostFilters $filters)
    {
        // Výber z archívu: mesiac sám o sebe nič neznamená, berie sa až
        // s rokom — tak, ako ich navigátor v bočnom paneli aj skladá.
        $year  = (int) request('rok') ?: null;
        $month = $year ? ((int) request('mesiac') ?: null) : null;

        $posts = $organization->posts()
            ->when($year, fn ($query) => $query->whereYear('created_at', $year))
            ->when($month, fn ($query) => $query->whereMonth('created_at', $month))
            // Rovnaké prepínače ako na úvodnej stránke (odporúčané,
            // najsledovanejšie, hľadanie), len zúžené na tento kanál.
            ->filter($filters)
            ->paginate(24)
            ->withQueryString();

        return view('organizations.index', [
            'organization'  => $organization,
            'posts'         => $posts,
            'year'          => $year,
            'month'         => $month,
        ] + $this->channelPanels($organization));
    }

    /**
     * Panely okolo výpisu kanála. Sú to krátke dopyty nad indexmi kanála,
     * preto stoja pri sebe — pohľad ich len vykreslí.
     */
    protected function channelPanels(Organization $organization)
    {
        return [
            'summary'       => $this->posts->organizationSummary($organization->id),
            'archive'       => $this->posts->organizationArchive($organization->id),
            'topPosts'      => $this->posts->mostViewedInOrganization($organization->id, null, 5),
            'comments'      => $this->posts->latestCommentsInOrganization($organization->id, 6),
            'commentsCount' => $this->posts->countCommentsInOrganization($organization->id),
        ];
    }
}
