<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Človek, ktorý v kanáloch pôsobí a vo videách vystupuje (kazateľ, kňaz,
 * hosť). Nie je to používateľ ani kanál, hoci môže mať vlastný kanál.
 */
class Person extends Model
{
    use SoftDeletes;

    // Bez toho by Eloquent hľadal tabuľku `people`.
    protected $table = 'persons';

    protected $fillable = [
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'title_suffix',
        'name',
        'normalized_name',
        'slug',
        'canal_id',
        'phone',
        'description',
        'email',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /** Podoba mena na porovnávanie: malé písmená bez diakritiky. */
    public static function normalize(string $name): string
    {
        return Str::lower(Str::ascii(trim(preg_replace('/\s+/u', ' ', $name))));
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withPivot(['source', 'matched_text', 'confidence'])
            ->withTimestamps();
    }

    /** Vlastný (osobný) kanál osoby. */
    public function canal(): BelongsTo
    {
        return $this->belongsTo(Canal::class);
    }

    /** Kanály (zbory, spoločenstvá), v ktorých osoba pôsobí. */
    public function canals(): BelongsToMany
    {
        return $this->belongsToMany(Canal::class)
            ->withPivot(['role', 'source', 'posts_count'])
            ->withTimestamps();
    }

    /** @return HasMany<PersonAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(PersonAlias::class);
    }
}
