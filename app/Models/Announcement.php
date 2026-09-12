<?php

namespace App\Models;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Oznam vypísaný superadminom. Na web ho vykresľuje App\View\Components\Announcements.
 */
class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'placement'       => AnnouncementPlacement::class,
        'variant'         => AnnouncementVariant::class,
        'active'          => 'boolean',
        'dismissible'     => 'boolean',
        'published_from'  => 'datetime',
        'published_until' => 'datetime',
    ];

    /** Zapnuté oznamy, ktoré sú práve v okne zobrazovania. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('published_from')
                ->orWhere('published_from', '<=', now()))
            ->where(fn (Builder $q) => $q
                ->whereNull('published_until')
                ->orWhere('published_until', '>=', now()));
    }

    public function scopePlacement(Builder $query, AnnouncementPlacement|string $placement): Builder
    {
        return $query->where(
            'placement',
            $placement instanceof AnnouncementPlacement ? $placement->value : $placement
        );
    }

    /**
     * Oznamy pre jedno miesto na stránke.
     *
     * Zámerne bez vyrovnávacej pamäte: config/cache.php zakazuje ukladať do
     * nej objekty (`serializable_classes => false`), takže uložená kolekcia
     * modelov by sa vrátila ako __PHP_Incomplete_Class. Dotaz stojí na indexe
     * (placement, active) nad tabuľkou s jednotkami riadkov.
     *
     * @return Collection<int, self>
     */
    public static function visibleFor(AnnouncementPlacement|string $placement): Collection
    {
        $placement = $placement instanceof AnnouncementPlacement
            ? $placement
            : AnnouncementPlacement::tryFrom($placement);

        if ($placement === null) {
            return new Collection();
        }

        return self::query()
            ->visible()
            ->placement($placement)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    /** Zobrazuje sa oznam práve teraz? Vo výpise administrácie odlišuje
     *  zapnutý oznam, ktorý ešte (alebo už) nie je v okne zobrazovania. */
    public function isRunning(): bool
    {
        return $this->active
            && ($this->published_from === null || $this->published_from->isPast())
            && ($this->published_until === null || $this->published_until->isFuture());
    }
}
