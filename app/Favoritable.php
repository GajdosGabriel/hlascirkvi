<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 26.06.2018
 * Time: 19:40
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

trait Favoritable
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
            $this->favorites()->delete(['user_id' => auth()->id()] );

            session()->flash('flash', 'OK Zrušené!');
        } else {
            $this->favorites()->create( ['user_id' => auth()->id()] );
            session()->flash('flash', 'OK Uložené!');
        }
    }

    public function isFavorited() {
        return ! ! $this->favorites->where('user_id', auth()->id())->count();
//        return $this->favorites()->whereUserId(auth()->id())->exists();
    }

    public function getIsFavoritedAttribute() {
        return $this->isFavorited();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }

}
