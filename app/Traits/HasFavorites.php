<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 26.06.2018
 * Time: 19:40
 */

namespace App\Traits;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

trait HasFavorites
{

    /**
     * Boot the trait.
     */
    protected static function bootFavoritable()
    {
        static::deleting(function ($model) {
            $model->favorites->each->delete();
        });
    }

    public function favorites() {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    public function favorite()
    {
        if ($this->favorites()->whereUserId(auth()->id() )->exists() ) {
            $this->favorites()->delete();

            session()->flash('flash', 'Zrušenie bolo úspešné!');
        } else {
            $this->favorites()->create( ['user_id' => auth()->id()] );
            session()->flash('flash', 'Príhlásenie bolo úspešné!');
        }
    }

    public function isFavorited() {
        // Neprihlásený návštevník nemôže mať nič obľúbené a user_id je NOT NULL,
        // takže pôvodný whereUserId(null) minul dopyt na istú nulu.
        if (! auth()->check()) {
            return false;
        }

        // Modely, ktoré atribút vypisujú, majú favorites v $with. Keď je väzba
        // načítaná, hľadáme v pamäti — inak každý riadok výpisu poslal vlastný
        // exists() dopyt.
        if ($this->relationLoaded('favorites')) {
            return $this->favorites->contains('user_id', auth()->id());
        }

        return $this->favorites()->whereUserId(auth()->id())->exists();
    }

    public function getIsFavoritedAttribute() {
        return $this->isFavorited();
    }

    public function getFavoritesCountAttribute()
    {
        // withCount('favorites') naplní favorites_count priamo v SELECTe.
        if (array_key_exists('favorites_count', $this->attributes)) {
            return (int) $this->attributes['favorites_count'];
        }

        return $this->favorites->count();
    }

}
