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
        if ($this->toggleFavorite()) {
            session()->flash('flash', 'Príhlásenie bolo úspešné!');
        } else {
            session()->flash('flash', 'Zrušenie bolo úspešné!');
        }
    }

    /**
     * Prepne označenie prihláseného užívateľa a vráti, či je teraz označené.
     *
     * Zrušenie volalo `$this->favorites()->delete()` bez podmienky na
     * užívateľa — jeden klik tak zmazal označenia všetkých ľudí naraz.
     */
    public function toggleFavorite(): bool
    {
        // user_id je NOT NULL; hosť by skončil na výnimke z databázy.
        abort_unless(auth()->check(), 401);

        $deleted = $this->favorites()->whereUserId(auth()->id())->delete();

        if (! $deleted) {
            $this->favorites()->create(['user_id' => auth()->id()]);
        }

        $this->unsetRelation('favorites');

        return ! $deleted;
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
