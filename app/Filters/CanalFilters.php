<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 21.09.2018
 * Time: 21:51
 */

namespace App\Filters;


use App\Models\Post;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CanalFilters extends Filters
{
    /** Koľko dní je kanál „nový“ (dlaždica a filter fresh). */
    public const FRESH_DAYS = 30;

    /** Po koľkých dňoch bez príspevku kanál „utíchol“. */
    public const SILENT_DAYS = 90;

    /** Radenia výpisu v administrácii: hodnota parametra => popis. */
    public const SORTS = [
        'newest' => 'Najnovšie registrované',
        'oldest' => 'Najstaršie registrované',
        'active' => 'Posledný príspevok',
        'posts' => 'Najviac príspevkov',
        'title' => 'Podľa názvu',
    ];

    protected $filters = ['search', 'type', 'unpublished', 'deletedAt', 'fresh', 'orphans', 'silent', 'youtubeOff', 'month', 'sort'];

    public function type($value)
    {
        if (! is_string($value) || ! in_array($value, \App\Enums\CanalType::values(), true)) {
            return $this->builder;
        }

        return $this->builder->where('canals.type', $value);
    }
    public function getFilters()
    {
        $filters = parent::getFilters();
        if ($this->request->has('publication')) {
            unset($filters['unpublished'], $filters['deletedAt']);
            $value = $this->request->query('publication');
            if (in_array($value, ['unpublished', 'deletedAt', 'published'], true)) {
                $filters[$value] = 1;
            }
        }

        return $filters;
    }

    public function published()
    {
        return $this->builder->whereNotNull('published');
    }
    public function search()
    {
        session()->flash('search', $this->request->search);
        return $this->builder
            ->where('title', 'LIKE', $this->likePattern($this->request->search))
            // ->orWhere('city', 'LIKE', $this->likePattern($this->request->search))
            ;
    }

    public function unpublished()
    {
         return $this->builder->whereNull('published');
    }

    public function deletedAt()
    {
         return $this->builder->onlyTrashed();
    }

    public function fresh()
    {
        return $this->builder->where('created_at', '>=', now()->subDays(self::FRESH_DAYS));
    }

    /** Kanály, ktoré nemá kto spravovať. */
    public function orphans()
    {
        return $this->builder->doesntHave('users');
    }

    public function silent()
    {
        $since = now()->subDays(self::SILENT_DAYS);

        return $this->builder->whereDoesntHave('posts', fn ($q) => $q->where('created_at', '>=', $since));
    }

    public function youtubeOff()
    {
        return $this->builder->whereNotNull('youtube_disabled_at');
    }

    /** Kanály registrované v mesiaci RRRR-MM (stĺpec grafu registrácií). */
    public function month($value)
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $value)) {
            return $this->builder;
        }

        $month = Carbon::createFromFormat('!Y-m', $value);

        return $this->builder->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
    }

    /**
     * Radenie podľa príspevkov ide cez vlastný podselekt, nie cez stĺpce
     * z withCount/withMax — filter používa aj výpis kanálov správcu, ktorý
     * tie agregáty nenačítava. Neznáma hodnota nerobí nič; o radení potom
     * rozhodne predvolené latest() vo Filters::apply().
     */
    public function sort($value)
    {
        $posts = fn () => Post::query()->whereColumn('posts.canal_id', 'canals.id');

        return match ($value) {
            'oldest' => $this->builder->oldest(),
            'active' => $this->builder->orderByDesc($posts()->select('created_at')->latest()->limit(1)),
            'posts' => $this->builder->orderByDesc($posts()->selectRaw('COUNT(*)')),
            'title' => $this->builder->orderBy('title'),
            default => $this->builder,
        };
    }


}
