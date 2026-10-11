<?php

namespace App\Models;

use App\Services\SystemLog\Recorder;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'favorited_id', 'favorited_type'];
    protected $hidden = ['pending_user_id'];

    /**
     * Pridanie sa zapisuje tu, lebo označenie vzniká na viacerých miestach
     * (prepnutie, explicitný stav, potvrdenie z e-mailu). Zrušenie ide dopytom
     * bez udalosti modelu — zapisuje ho App\Traits\HasFavorites.
     */
    protected static function booted(): void
    {
        static::created(function (self $favorite) {
            if ($favorite->user_id) {
                self::log($favorite->favorited, (int) $favorite->user_id, true);
            }
        });
    }

    public static function log(?Model $model, int $userId, bool $added): void
    {
        $what = match (true) {
            $model instanceof Post => 'príspevok',
            $model instanceof Prayer => 'modlitba',
            $model instanceof Comment => 'komentár',
            $model instanceof Canal => 'kanál',
            default => 'záznam',
        };
        $title = $model instanceof Comment
            ? mb_substr(trim(strip_tags((string) $model->body)), 0, 80)
            : (string) ($model?->title ?? '');

        Recorder::activity('favorite', $added ? 'added' : 'removed',
            ($added ? 'Označené' : 'Označenie zrušené') . " ({$what})" . ($title !== '' ? ': ' . $title : ''),
            $model, $userId,
        );
    }

    public function favorited()
    {
        return $this->morphTo();
    }


}
