<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'title_suffix',
        'name',
        'normalized_name',
        'phone',
        'description',
        'email',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withPivot(['source', 'matched_text', 'confidence'])
            ->withTimestamps();
    }
}
