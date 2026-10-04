<?php

namespace App\Http\Controllers\Public;

use App\Enums\PostSection;
use Cache;
use \Alaouy\Youtube;
use App\Models\Seminar;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
        //
    }


    public function zivePrenosy(EloquentPostRepository $posts)
    {
        session()->forget('lastVisit');

        session()->forget('countUnwatchedVideos');
        $posts = $posts->groupedBySection(PostSection::Live);
        return view('pages.online-prenosy', compact('posts'));
    }

    public function seminare(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'regex:/^(?:19\d{2}|20\d{2}|unknown)$/'],
        ]);
        $search = trim($filters['q'] ?? '');
        $year = $filters['year'] ?? '';
        $catalog = Seminar::published()->where('kind', 'seminar')->orderByDesc('created_at')->get();
        $years = $catalog->pluck('archive_year')->filter()->unique()->sortDesc()->values();
        $hasUnknownYear = $catalog->contains(fn ($seminar) => $seminar->archive_year === null);
        $ids = $catalog->filter(fn ($seminar) => $year === ''
            || ($year === 'unknown' ? $seminar->archive_year === null : $seminar->archive_year === (int) $year))->modelKeys();
        $term = '%'.addcslashes($search, '\\%_').'%';
        $matchingSeries = Seminar::whereIn('id', $ids)->when($search !== '', fn ($query) => $query
            ->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('description', 'like', $term)
                ->orWhereHas('canal', fn ($canal) => $canal->where('title', 'like', $term))))->pluck('id');
        $recordings = fn ($query) => $query->published()->available()
            ->when($search !== '', fn ($q) => $q->where(fn ($titles) => $titles->where('posts.title', 'like', $term)
                ->orWhereIn('post_seminar.seminar_id', $matchingSeries)));

        $seminars = Seminar::whereIn('id', $ids)
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q->whereIn('id', $matchingSeries)
                ->orWhereHas('posts', fn ($posts) => $posts->published()->available()->where('posts.title', 'like', $term))))
            ->withCount(['posts' => fn ($query) => $query->published()->available()])
            ->with(['posts' => fn ($query) => $recordings($query)->orderBy('posts.created_at')->orderBy('posts.id')->limit(4)])
            ->orderBy('created_at', 'desc')->get();

        return view('pages.seminare', compact('seminars', 'search', 'year', 'years', 'hasUnknownYear'));
    }

    public function gdpr()
    {
        return view('pages.ochrana-osobnych-udajov');
    }
}
