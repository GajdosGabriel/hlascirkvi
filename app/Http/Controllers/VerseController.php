<?php

namespace App\Http\Controllers;

use App\Models\Verse;

class VerseController extends Controller
{
    public function index($slug=null) {

        // Neznámy slug alebo diera v ID vracali 500 — ->first() a ->find() môžu
        // vrátiť null a hneď sa na ňom čítalo ->id / ->slug. Podmienka pre
        // prvý záznam bola navyše `$id < 1`, čo pri ID z databázy nikdy neplatí.
        $verse = $slug === null
            ? Verse::today()
            : Verse::whereSlug($slug)->first();

        abort_if($verse === null, 404);

        $id = $verse->id;

        $previous = optional(Verse::find($id - 1))->slug
            ?? optional(Verse::orderByDesc('id')->first())->slug;

        $next = optional(Verse::find($id + 1))->slug
            ?? optional(Verse::orderBy('id')->first())->slug;

        // Dnešné zamyslenie nesie dnešný dátum (aj 29. február, ktorý zopakuje
        // 28.); vybrané zamyslenie dátum svojho dňa (mesiac a deň) v tomto roku.
        $date = ($slug === null ? now() : Verse::dateFor($id))
            ->locale(config('app.locale'))
            ->translatedFormat('j. F Y');

        return view('verses.show')
            ->with('previous', $previous)
            ->with('next', $next)
            ->with('post', $verse)
            ->with('date', $date)
            ->with('title', trans('web.daily_read'));
    }

}
