<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Verse;
use Illuminate\Http\Request;

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

        $date = $this->date_from_day_of_year(null, $id);

        return view('verses.show')
            ->with('previous', $previous)
            ->with('next', $next)
            ->with('post', $verse)
            ->with('date', $date)
            ->with('title', trans('web.daily_read'));
    }


    /**
     * Create Localized Date from Day of Year
     *
     * Create custom date according to locale settings
     *
     * @param   string   $format
     * @param   integer  $dayOfYear
     * @return  string
     */
    function date_from_day_of_year($format = null, $dayOfYear = null, $year = null)
    {
        $offset = ($dayOfYear ?? now()->dayOfYear) - 1;

        return Carbon::create($year ?? now()->year, 1, 1, 0)
            ->addDays($offset)->locale(config('app.locale'))
            ->translatedFormat($format ?? 'j. F Y');
    }

}
