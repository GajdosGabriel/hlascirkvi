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

    // Model sa do 9/2026 volal Organization a tabuľka `organizations` mu
    // ostala ešte dlho po premenovaní — model preto musel mať $table aj
    // vlastný getForeignKey(). Od migrácie
    // 2026_09_16_120000_rename_organizations_to_canals sedí všetko na
    // konvencii (canals, canal_id, canal_user), takže tu netreba nič.

    protected $guarded = ['id'];

    protected $casts = [
        'published' => 'datetime',
        'title' => \App\Casts\StringLength255::class,
        'url_www' => \App\Casts\Urlwww::class,
        'youtube_disabled_at' => 'datetime',
        'front_listed_at' => 'datetime',
        'denomination' => \App\Enums\Denomination::class,
        'type' => \App\Enums\CanalType::class,
        'post_section' => \App\Enums\CanalSection::class,
        'import_day' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $canal) {
            if (! array_key_exists('published', $canal->getAttributes())) {
                $canal->published = now();
            }
        });
    }

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

    /**
     * Smajlíky a emoji v názve kanála (CanalRequest ich odmieta). Rozsahy
     * pokrývajú emoji, symboly, dingbaty, vlajky a variačný selektor.
     */
    public const EMOJI_PATTERN = '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{1F1E6}-\x{1F1FF}\x{FE0F}\x{200D}]/u';

    /**
     * Voľný názov kanála: bez emoji a pri zhode s existujúcim kanálom
     * (vrátane zmazaných) s poradovým číslom — „Mária Mária (2)".
     * Porovnanie robí databáza, takže platí jej collation bez diakritiky
     * a veľkosti písmen, rovnako ako pravidlo unique v CanalRequest.
     */
    public static function uniqueTitle(string $title, ?int $ignoreId = null): string
    {
        $base = trim(preg_replace('/\s+/u', ' ', preg_replace(self::EMOJI_PATTERN, '', $title)));
        $base = mb_strlen($base) >= 2 ? $base : 'Kanál';

        $taken = fn (string $candidate) => static::withTrashed()
            ->where('title', $candidate)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        $candidate = $base;
        for ($n = 2; $taken($candidate); $n++) {
            $candidate = $base . ' (' . $n . ')';
        }

        return $candidate;
    }

    protected $appends = ['favoritesCount', 'isFavorited', 'initialName'];

    // favoritesCount aj isFavorited sú v $appends, takže sa počítajú pri každej
    // serializácii kanála. Bez načítanej väzby si každý kanál vypýtal vlastné
    // dva dopyty; takto ich Eloquent načíta pre celú dávku naraz.
    protected $with = ['favorites'];


    /**
     * Kanály predného zoznamu, abecedne. Poradie na karte v bočnom paneli
     * určuje záujem návštevníkov (App\Services\FrontList\FrontList).
     *
     * Zmazaný kanál odfiltruje SoftDeletes, skrytý (`published` IS NULL) táto
     * podmienka. Pôvodný surový dopyt nekontroloval ani jedno a zoznam takýto
     * kanál pokojne ponúkal ďalej.
     */
    public function scopeOnFrontList($query)
    {
        return $query->whereNotNull('front_listed_at')
            ->whereNotNull('published')
            ->orderBy('title');
    }

    /**
     * Kanály, ktoré admin nepozastavil (CanalSection::Paused). Na rozdiel od
     * `youtube_disabled_at`, ktoré zapína aj ruší import sám, pozastavenie
     * prepína len admin vo formulári kanála.
     */
    public function scopeNotPaused($query)
    {
        return $query->where('post_section', '<>', \App\Enums\CanalSection::Paused->value);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function prayers()
    {
        return $this->hasMany(Prayer::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'canal_id');
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
