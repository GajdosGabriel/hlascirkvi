<?php

namespace App\Services\FrontList;

use App\Models\Canal;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Jeden riadok predného zoznamu.
 *
 * Zoznam sa drží v cache a config/cache.php má `serializable_classes` na
 * `false` — do cache teda nesmie ísť žiadny objekt, nieto celý model kanála
 * s väzbami. Preto sa ukladá pole a tento typ ho zase poskladá; karta aj
 * verejná stránka tak čítajú rovnaký tvar a nemusia vedieť, odkiaľ prišiel.
 *
 * @implements Arrayable<string, mixed>
 */
final class FrontListItem implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $avatar,
        public readonly int $postsCount,
        public readonly ?Carbon $lastPostAt,
    ) {
    }

    public static function fromCanal(Canal $canal): self
    {
        return new self(
            id:         (int) $canal->id,
            title:      (string) $canal->title,
            slug:       $canal->slug,
            avatar:     $canal->avatar,
            postsCount: (int) ($canal->posts_count ?? 0),
            lastPostAt: $canal->last_post_at ? Carbon::parse($canal->last_post_at) : null,
        );
    }

    /** @param  array<string, mixed>  $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id:         (int) $row['id'],
            title:      (string) $row['title'],
            slug:       $row['slug'] ?? null,
            avatar:     $row['avatar'] ?? null,
            postsCount: (int) ($row['postsCount'] ?? 0),
            lastPostAt: isset($row['lastPostAt']) ? Carbon::parse($row['lastPostAt']) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'slug'       => $this->slug,
            'avatar'     => $this->avatar,
            'postsCount' => $this->postsCount,
            'lastPostAt' => $this->lastPostAt?->toDateTimeString(),
        ];
    }

    /**
     * Adresa obrázka, alebo nič. Kanál si avatar nesie len ako meno súboru
     * a ten na disku chýbať môže — preto ho šablóny vykresľujú s onerror.
     */
    public function avatarUrl(): ?string
    {
        return $this->avatar
            ? Storage::url('organizations/' . $this->id . '/' . $this->avatar)
            : null;
    }

    /** Iniciály mena ako náhrada za chýbajúci obrázok. */
    public function initials(): string
    {
        $skratka = '';

        foreach (explode(' ', $this->title) as $slovo) {
            $skratka .= mb_substr($slovo, 0, 1, 'utf-8');
        }

        return $skratka;
    }

    /**
     * Kanál, z ktorého už dlho nič nevyšlo. Na titulke roky viseli kanály
     * s posledným príspevkom z roku 2017.
     */
    public function isStale(): bool
    {
        return $this->lastPostAt === null
            || $this->lastPostAt->lt(now()->subMonths((int) config('frontlist.stale_after_months')));
    }
}
