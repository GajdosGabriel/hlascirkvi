<?php

namespace App\Http\Controllers\Canal;


use App\Models\Seminar;
use App\Models\Canal;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\SaveSeminarRequest;
use App\Http\Controllers\Controller;

class CanalSeminarController extends Controller
{
    public function index(Canal $canal)
    {
        $this->authorize('manage', $canal);

        $seminars = $canal->seminars()->withCount('posts')
            ->orderBy('created_at', 'desc')->get();

        return view('profiles.seminars.index', ['seminars' => $seminars, 'canal' => $canal]);
    }

    public function show(Canal $canal, Seminar $seminar, Request $request)
    {
        $this->authorize('view', $seminar);

        $filters = $request->validate(['q' => 'nullable|string|max:200', 'membership' => 'nullable|in:all,in,out']);
        $search = trim($filters['q'] ?? '');
        $membership = $filters['membership'] ?? 'all';
        // Staršie playlisty môžu mať príspevky iného kanála. Ich väzby sa dajú
        // odobrať, nové ručné pridanie však zostáva obmedzené na vlastný kanál.
        $posts = Post::where(fn ($q) => $q->where('canal_id', $canal->id)
            ->orWhereHas('seminars', fn ($s) => $s->whereKey($seminar->id)))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', '%'.addcslashes($search, '\\%_').'%'))
            ->when($membership === 'in', fn ($q) => $q->whereHas('seminars', fn ($s) => $s->whereKey($seminar->id)))
            ->when($membership === 'out', fn ($q) => $q->whereDoesntHave('seminars', fn ($s) => $s->whereKey($seminar->id)))
            ->withExists(['seminars as in_collection' => fn ($q) => $q->whereKey($seminar->id)])
            ->latest('posts.id')->paginate(30)->withQueryString();

        return view('profiles.seminars.show', compact('canal', 'seminar', 'posts', 'search', 'membership'));
    }

    public function posts(Canal $canal, Seminar $seminar, Request $request)
    {
        $this->authorize('update', $seminar);
        $data = $request->validate([
            'action' => 'required|in:add,remove',
            'posts' => 'required|array|min:1|max:100',
            'posts.*' => ['required', 'integer', 'distinct', Rule::exists('posts', 'id')
                ->whereNull('deleted_at')->where('youtube_blocked', 0)
                ->where(function ($query) use ($request, $canal, $seminar) {
                    if ($request->input('action') === 'remove') {
                        $query->whereIn('id', \Illuminate\Support\Facades\DB::table('post_seminar')
                            ->select('post_id')->where('seminar_id', $seminar->id));
                    } else {
                        $query->where('canal_id', $canal->id);
                    }
                })],
        ], ['posts.required' => 'Najprv označte príspevky.', 'posts.*.exists' => 'Vyberte príspevky tohto kanála.']);

        if ($data['action'] === 'add') {
            $seminar->posts()->syncWithoutDetaching($data['posts']);
        } else {
            $seminar->posts()->detach($data['posts']);
        }

        return redirect()->route('profile.canals.seminars.show', [$canal, $seminar])
            ->with('flash', $data['action'] === 'add' ? 'Príspevky boli pridané do kolekcie.' : 'Príspevky boli odobraté z kolekcie.');
    }


    public function create(Canal $canal)
    {
        // Rovnaká kontrola ako pri uložení — predtým formulár otvoril
        // ktokoľvek a odmietnutie prišlo až po jeho vyplnení.
        $this->authorize('manage', $canal);
        return view('seminars.create', ['seminar' => new Seminar(['kind' => request('kind') === 'seminar' ? 'seminar' : 'collection']), 'canal' => $canal]);
    }

    public function edit(Canal $canal, Seminar $seminar)
    {
        $this->authorize('update', $seminar);
        return view('seminars.edit', compact('seminar', 'canal'));
    }

    public function store(Canal $canal, SaveSeminarRequest $request)
    {
        // Autorizácia tu chýbala úplne — a `canal_id` sa bralo z
        // prihláseného užívateľa, nie z routy, takže sa seminár vždy založil
        // pod jeho primárnym kanálom bez ohľadu na to, kde bol formulár.
        //
        // `manage` (zoznam správcov), nie `update` (aktívny kanál): semináre
        // ďalej upravuje a maže každý správca kanála (SeminarPolicy::owns),
        // no založiť ich šlo len v práve aktívnom kanáli — správca iného kanála
        // vyplnil formulár a dostal 403.
        $this->authorize('manage', $canal);

        $seminar = $canal->seminars()->create($request->validated());

        return redirect()->route('profile.canals.seminars.show', [$canal, $seminar])
            ->with('flash', 'Kolekcia bola vytvorená. Teraz do nej môžete pridať príspevky.');
    }

    public function update(Canal $canal, Seminar $seminar, SaveSeminarRequest $request)
    {
        $this->authorize('update', $seminar);

        $seminar->update($request->validated());

        if (request()->expectsJson()) {
            return $seminar;
        };

        return redirect()->route('profile.canals.seminars.index', $canal->id);
    }



    public function destroy(Canal $canal, Seminar $seminar)
    {
        $this->authorize('delete', $seminar);

        $seminar->posts()->detach();
        $seminar->delete();
        return redirect()->route('profile.canals.seminars.index', $canal->id);
    }
}
