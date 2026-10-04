<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function create(Request $request)
    {
        if ($request->filled('canal_id')) {
            $data = $request->validate(['canal_id' => ['required', 'integer']]);
            $canal = Canal::findOrFail($data['canal_id']);

            return redirect()->route('profile.canals.seminars.create', $canal);
        }

        $canals = Canal::orderBy('title')->get(['id', 'title']);

        return view('admins.seminars.create', compact('canals'));
    }
}
