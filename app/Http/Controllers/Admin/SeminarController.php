<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSeminarRequest;
use App\Models\Canal;
use App\Models\Seminar;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $seminars = Seminar::query()->with('canal')->withCount('posts')
            ->whereHas('canal')
            ->when(in_array($request->input('kind'), ['seminar', 'collection'], true), fn ($query) => $query->where('kind', $request->input('kind')))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhereHas('canal', fn ($query) => $query->where('title', 'like', '%'.$search.'%'));
            }))
            ->when($request->input('status') === 'published', fn ($query) => $query->whereNotNull('published'))
            ->when($request->input('status') === 'unpublished', fn ($query) => $query->whereNull('published'))
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admins.seminars.index', compact('seminars'));
    }

    /**
     * Nová kolekcia priamo v administrácii. Do 10/2026 sa tu len vyberal
     * kanál a formulár presmeroval do správy kanála, mimo administrácie.
     */
    public function create()
    {
        $canals = Canal::orderBy('title')->get(['id', 'title']);
        $seminar = new Seminar(['kind' => request('kind') === 'seminar' ? 'seminar' : 'collection']);

        return view('admins.seminars.create', compact('canals', 'seminar'));
    }

    public function store(SaveSeminarRequest $request)
    {
        $data = $request->validate(['canal_id' => ['required', 'integer', 'exists:canals,id']]);
        $canal = Canal::findOrFail($data['canal_id']);

        $seminar = $canal->seminars()->create($request->validated());

        // Príspevky sa do kolekcie pridávajú v jej správe — tá je len jedna.
        return redirect()->route('profile.canals.seminars.show', [$canal, $seminar])
            ->with('flash', 'Kolekcia bola vytvorená. Teraz do nej môžete pridať príspevky.');
    }
}
