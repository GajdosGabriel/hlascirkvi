<?php

namespace App\Models;

use App\Models\Prayer;
use Illuminate\Support\Str;
use App\Services\PhoneSanitizer;
use App\Traits\HasComments;
use App\Traits\HasFavorites;
use App\Traits\HasFilter;
use App\Traits\HasImages;
use App\Traits\Datetime;
use App\Traits\HasDatetime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Casts\Urlwww;

class Canal extends Model
{
    use Notifiable, SoftDeletes, HasFactory, HasFavorites, HasImages, HasFilter, HasComments, HasDatetime;

    // Model sa do 9/2026 volal Organization a databáza pomenovanie drží dodnes:
    // tabuľka organizations, cudzie kľúče organization_id, pivot
    // organization_user. Bez týchto nastavení by si Eloquent odvodil
    // canals / canal_id / canal_user.
    // Polymorfné stĺpce (favorites.favorited_type...) pokrýva morph mapa
    // v AppServiceProvider.
    protected $table = 'organizations';

    protected $guarded = ['id'];

    public function getForeignKey()
    {
        return 'organization_id';
    }

    protected $casts = [
        'title' => \App\Casts\StringLength255::class,
        'url_www' => \App\Casts\Urlwww::class,
        'youtube_disabled_at' => 'datetime',
        'front_listed_at' => 'datetime',
        'denomination' => \App\Enums\Denomination::class,
        'post_section' => \App\Enums\CanalSection::class,
        'import_day' => 'integer',
    ];

    /**
     * Dni, v ktoré denný beh hľadá kanál na YouTube podľa mena. Číslovanie
     * kopíruje Carbon::dayOfWeek, aby sa dopyt dal poskladať bez prekladu.
     */
    public const IMPORT_DAYS = [
        0 => 'Nedeľa',
        1 => 'Pondelok',
        2 => 'Utorok',
        3 => 'Streda',
        4 => 'Štvrtok',
        5 => 'Piatok',
        6 => 'Sobota',
    ];

    protected $appends = ['favoritesCount', 'isFavorited', 'initialName'];

    // favoritesCount aj isFavorited sú v $appends, takže sa počítajú pri každej
    // serializácii kanála. Bez načítanej väzby si každý kanál vypýtal vlastné
    // dva dopyty; takto ich Eloquent načíta pre celú dávku naraz.
    protected $with = ['favorites'];


    /**
     * Kanály predného zoznamu na úvodnej stránke, v poradí, ktoré im určil
     * správca. Kanál bez poradia ide na koniec — pridať jeden kanál teda
     * neznamená prečíslovať celý zoznam.
     *
     * Zmazaný kanál odfiltruje SoftDeletes, skrytý (`published` = 0) táto
     * podmienka. Pôvodný surový dopyt nekontroloval ani jedno a zoznam takýto
     * kanál pokojne ponúkal ďalej.
     */
    public function scopeOnFrontList($query)
    {
        return $query->whereNotNull('front_listed_at')
            ->where('published', 1)
            ->orderByRaw('front_position is null, front_position')
            ->orderBy('title');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function prayers()
    {
        return $this->hasMany(Prayer::class);
    }

    public function messengers()
    {
        return $this->hasMany(Messenger::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'org_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function seminars()
    {
        return $this->hasMany(Seminar::class);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst($value);
        $this->attributes['slug']  = Str::slug($value);
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] =  (new PhoneSanitizer($value))->getSanitized();

        $phone_numeric = filter_var($this->attributes['phone'], FILTER_SANITIZE_NUMBER_INT);
        $phone_numeric = str_replace(['+', '-'], '', $phone_numeric);

        $this->attributes['phone_numeric'] = $phone_numeric;
    }

    public function anonymizer()
    {
        return strtok($this->title, " ");
    }


    // Inicialy mena
    public function getInitialNameAttribute()
    {
        $acronym = '';
        foreach (explode(' ', $this->title) as $word) $acronym .= mb_substr($word, 0, 1, 'utf-8');
        return $acronym;
    }
}
