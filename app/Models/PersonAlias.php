<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Iná podoba mena osoby (bez diakritiky, skloňovaná, prezývka). */
class PersonAlias extends Model
{
    protected $fillable = ['person_id', 'name', 'normalized_name'];

    protected static function booted(): void
    {
        static::saving(function (self $alias) {
            $alias->normalized_name = Person::normalize($alias->name);
        });
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
