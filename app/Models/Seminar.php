<?php

namespace App\Models;

use App\Traits\HasCanal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seminar extends Model
{
    use  SoftDeletes, HasFactory, HasCanal;
    protected $casts = ['title' => \App\Casts\StringLength255::class];
    protected $guarded = ['id'];
    // public $timestamps = false;

    protected $with = ['canal'];

    public function scopePublished($query)
    {
        return $query->whereNotNull('published')->where('published', '<=', now())
            ->whereHas('canal', fn ($canal) => $canal->whereNotNull('published'));
    }

    public function getKindLabelAttribute(): string
    {
        return $this->kind === 'collection' ? 'Tematická kolekcia' : 'Seminár / podujatie';
    }

    public function getArchiveYearAttribute(): ?int
    {
        // Rok importu nie je ročník podujatia. Bez jednoznačného ročníka
        // v názve necháme sériu vo výbere „Bez uvedeného roku“.
        preg_match_all('/\b(?:19|20)\d{2}\b/u', $this->title, $matches);
        $years = array_unique($matches[0]);

        return count($years) === 1 ? (int) reset($years) : null;
    }


    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
