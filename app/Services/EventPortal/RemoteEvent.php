<?php

namespace App\Services\EventPortal;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * Jedno podujatie tak, ako ho vráti API portálu Event.
 *
 * Zámerne to nie je Eloquent model — nič sa neukladá, dáta žijú len v cache.
 * Trieda existuje preto, aby blade šablóny nepracovali s holým poľom a aby
 * bolo na jednom mieste vidieť, na ktoré kľúče API sa spoliehame. Keď sa
 * v API niečo premenuje, opravuje sa to tu, nie v desiatich šablónach.
 */
class RemoteEvent implements Arrayable
{
    /** @var array<string, mixed> */
    protected array $data;

    /** @param array<string, mixed> $data */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** @param array<string, mixed> $data */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /* ---------------------------------------------------------------- základ */

    public function id(): int
    {
        return (int) ($this->data['id'] ?? 0);
    }

    public function title(): string
    {
        return (string) ($this->data['name'] ?? '');
    }

    public function slug(): string
    {
        $slug = (string) ($this->data['slug'] ?? '');

        return $slug !== '' ? $slug : (Str::slug($this->title()) ?: 'podujatie');
    }

    /** Detail u nás — /akcie/{id}/{slug}. */
    public function url(): string
    {
        return route('event.show', [$this->id(), $this->slug()]);
    }

    /** Ten istý detail na portáli, kde beží prihlasovanie a vstupenky. */
    public function portalUrl(): string
    {
        return config('eventportal.url') . '/akcie/' . $this->id() . '/' . $this->slug();
    }

    /** Surové HTML popisu z API. Na výpis slúži bodyHtml(). */
    public function body(): string
    {
        return (string) ($this->data['body'] ?? '');
    }

    /**
     * Popis pripravený na vypísanie. Portál posiela redakčné HTML (h3, p, ul,
     * strong, odkazy) — necháme len tieto značky, nech sa cudzím obsahom
     * nedá rozbiť stránka ani prepašovať skript.
     */
    public function bodyHtml(): string
    {
        return strip_tags(
            $this->body(),
            '<p><br><h2><h3><h4><strong><b><em><i><u><ul><ol><li><a><blockquote><hr>'
        );
    }

    public function excerpt(int $length = 220): string
    {
        $text = html_entity_decode(strip_tags($this->body()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        return Str::limit($text, $length);
    }

    /* ---------------------------------------------------------------- termín */

    /**
     * API vracia UTC (končí na Z), portál aj my zobrazujeme slovenský čas —
     * bez prepnutia zóny by bolo každé podujatie o dve hodiny skôr.
     */
    public function startAt(): ?Carbon
    {
        return $this->date('start_at');
    }

    public function endAt(): ?Carbon
    {
        return $this->date('end_at');
    }

    /** @var array<string, ?Carbon> Rozparsované dátumy, aby sa nerobili opakovane. */
    protected array $dates = [];

    /**
     * startAt() sa v triede volá na tucte miest a schemaOrg() k tomu pridá
     * ďalšie — vykreslenie jednej karty tak spustilo desiatku Carbon::parse().
     * Pri výpise päťdesiatich podujatí to boli stovky zbytočných parseov.
     */
    protected function date(string $key): ?Carbon
    {
        // Carbon je meniteľný a šablóny na ňom volajú ->locale('sk'), preto ide
        // von kópia — uložená inštancia zostáva nedotknutá. Kopírovanie je proti
        // parsovaniu zanedbateľné.
        if (array_key_exists($key, $this->dates)) {
            return $this->dates[$key]?->copy();
        }

        $raw = $this->data[$key] ?? null;

        if (empty($raw)) {
            return $this->dates[$key] = null;
        }

        try {
            $this->dates[$key] = Carbon::parse($raw)->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            $this->dates[$key] = null;
        }

        return $this->dates[$key]?->copy();
    }

    /** Kľúč dňa pre zoskupenie vo výpise (2026-09-07). */
    public function dayKey(): string
    {
        return $this->startAt()?->toDateString() ?? 'bez-terminu';
    }

    /** Hotový popis termínu z API — "07. 09. 2026 16:00 - 17:30". */
    public function dateRangeLabel(): ?string
    {
        return $this->data['date_range_label'] ?? null;
    }

    /**
     * Slovenské tvary dátumu. Carbon ich vie sám, len sa mu musí povedať
     * jazyk — appka beží pod `sk`, ale Carbon si locale drží zvlášť.
     */
    public function dayName(): ?string
    {
        return $this->startAt()?->locale('sk')->isoFormat('dddd');
    }

    public function dayNumber(): ?string
    {
        return $this->startAt()?->format('d');
    }

    public function monthShort(): ?string
    {
        return $this->startAt()?->locale('sk')->isoFormat('MMM');
    }

    /** "7. septembra 2026" */
    public function longDate(): ?string
    {
        return $this->startAt()?->locale('sk')->isoFormat('D. MMMM YYYY');
    }

    /** Len čas začiatku, prípadne rozsah v rámci jedného dňa. */
    public function timeLabel(): ?string
    {
        $start = $this->startAt();

        if (! $start) {
            return null;
        }

        $end = $this->endAt();

        if ($end && ! $this->isMultiDay() && $end->format('H:i') !== $start->format('H:i')) {
            return $start->format('H:i') . ' – ' . $end->format('H:i');
        }

        return $start->format('H:i');
    }

    public function isMultiDay(): bool
    {
        $start = $this->startAt();
        $end = $this->endAt();

        return $start !== null && $end !== null && ! $start->isSameDay($end);
    }

    public function isOngoing(): bool
    {
        $start = $this->startAt();
        $end = $this->endAt();

        return $start !== null && $end !== null && $start->isPast() && $end->isFuture();
    }

    public function isPast(): bool
    {
        $end = $this->endAt() ?? $this->startAt();

        return $end !== null && $end->isPast();
    }

    public function isToday(): bool
    {
        return (bool) $this->startAt()?->isToday();
    }

    public function isTomorrow(): bool
    {
        return (bool) $this->startAt()?->isTomorrow();
    }

    public function registrationDeadlineAt(): ?Carbon
    {
        return $this->date('registration_deadline_at');
    }

    /* ---------------------------------------------------------------- obrázok */

    public function poster(string $variant = 'large'): ?string
    {
        $image = $this->data['primary_image'] ?? null;

        if (! is_array($image)) {
            $thumb = $this->data['thumb_image'] ?? null;

            return $thumb ? (string) $thumb : null;
        }

        $url = $image[$variant] ?? $image['large'] ?? $image['thumb'] ?? null;

        return $url ? (string) $url : null;
    }

    public function thumb(): ?string
    {
        return $this->poster('thumb');
    }

    /**
     * Portál dopĺňa podujatiam bez plagátu vlastný zástupný obrázok (SVG).
     * Ten u nás nechceme — máme vlastnú grafickú výplň, ktorá sadne dizajnu.
     */
    public function hasPoster(): bool
    {
        $url = $this->poster();

        return $url !== null && ! Str::endsWith($url, '.svg');
    }

    /* ------------------------------------------------------------- kto a kde */

    public function organizer(): ?string
    {
        return $this->data['canal']['name'] ?? null;
    }

    public function organizerWebsite(): ?string
    {
        return $this->data['canal']['website'] ?? null;
    }

    public function venue(): ?string
    {
        return $this->data['venue']['name'] ?? null;
    }

    public function venueStreet(): ?string
    {
        return $this->data['venue']['street'] ?? null;
    }

    public function municipality(): ?string
    {
        return $this->data['municipality']['shortname']
            ?? $this->data['municipality']['fullname']
            ?? null;
    }

    public function municipalitySlug(): ?string
    {
        return $this->data['municipality']['slug'] ?? null;
    }

    /** Adresa do jedného riadku — pre kartu aj pre odkaz na mapu. */
    public function address(): ?string
    {
        $parts = array_filter([
            $this->venue(),
            $this->venueStreet(),
            $this->municipality() !== $this->venue() ? $this->municipality() : null,
        ]);

        return $parts !== [] ? implode(', ', array_unique($parts)) : null;
    }

    public function latitude(): ?float
    {
        $value = $this->data['venue']['latitude'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function longitude(): ?float
    {
        $value = $this->data['venue']['longitude'] ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function mapUrl(): ?string
    {
        if ($this->latitude() !== null && $this->longitude() !== null) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . $this->latitude() . ',' . $this->longitude();
        }

        $address = $this->address();

        return $address
            ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address)
            : null;
    }

    /* --------------------------------------------------------------- ostatné */

    /** @return array<int, array<string, mixed>> */
    public function tags(): array
    {
        $tags = $this->data['tags'] ?? [];

        return is_array($tags) ? $tags : [];
    }

    public function price(): ?string
    {
        $amount = $this->data['price_amount'] ?? null;

        if ($amount === null || $amount === '') {
            return null;
        }

        if ((float) $amount <= 0.0) {
            return 'Vstup voľný';
        }

        $formatted = number_format((float) $amount, 2, ',', ' ');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return $formatted . ' ' . ($this->data['price_currency'] ?? 'EUR');
    }

    public function isFree(): bool
    {
        $amount = $this->data['price_amount'] ?? null;

        return $amount !== null && $amount !== '' && (float) $amount <= 0.0;
    }

    public function ticketsEnabled(): bool
    {
        return (bool) ($this->data['tickets_enabled'] ?? false);
    }

    /** Odkaz na registráciu alebo stránku podujatia, ktorý zadal organizátor. */
    public function website(): ?string
    {
        $url = $this->data['website'] ?? null;

        return $url ? (string) $url : null;
    }

    /** Článok alebo zdroj, z ktorého portál podujatie naimportoval. */
    public function sourceUrl(): ?string
    {
        $url = $this->data['orginal_source'] ?? null;

        return $url ? (string) $url : null;
    }

    /** @return array<string, string> */
    public function calendarLinks(): array
    {
        $links = $this->data['calendar_links'] ?? [];

        return is_array($links) ? $links : [];
    }

    /** @return array<int, array<string, mixed>> */
    public function seriesOccurrences(): array
    {
        $items = $this->data['series_occurrences'] ?? [];

        return is_array($items) ? $items : [];
    }

    /** Ďalšie termíny tej istej série, ktoré vo výpise zastupuje táto karta. */
    public function seriesUpcomingCount(): int
    {
        return (int) ($this->data['series_upcoming_count'] ?? 0);
    }

    /**
     * Prílohy okrem hlavného plagátu — programy, prihlášky, mapky.
     *
     * @return array<int, array<string, mixed>>
     */
    public function attachments(): array
    {
        $files = $this->data['files'] ?? [];

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn ($file) => is_array($file)
                && ! ($file['is_primary'] ?? false)
                && ($file['type'] ?? null) !== 'image'
        ));
    }

    /**
     * Obrázky do galérie pod popisom (bez hlavného plagátu).
     *
     * @return array<int, array<string, mixed>>
     */
    public function gallery(): array
    {
        $files = $this->data['files'] ?? [];

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn ($file) => is_array($file)
                && ($file['type'] ?? null) === 'image'
                && ! ($file['is_primary'] ?? false)
        ));
    }

    /**
     * Podujatie v schema.org — vďaka nemu vie Google zobraziť termín a miesto
     * priamo vo výsledku hľadania.
     *
     * Skladá sa v PHP a nie v šablóne zámerne: kľúče ako `@context` by Blade
     * v .blade.php čítal ako svoje direktívy a šablóna by sa neskompilovala.
     *
     * @return array<string, mixed>
     */
    public function schemaOrg(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $this->title(),
            'description' => $this->excerpt(300),
            'url' => $this->url(),
            'eventStatus' => 'https://schema.org/EventScheduled',
        ];

        if ($this->startAt()) {
            $schema['startDate'] = $this->startAt()->toIso8601String();
        }

        if ($this->endAt()) {
            $schema['endDate'] = $this->endAt()->toIso8601String();
        }

        if ($this->hasPoster()) {
            $schema['image'] = [$this->poster()];
        }

        if ($this->address()) {
            $schema['location'] = [
                '@type' => 'Place',
                'name' => $this->venue() ?: $this->municipality(),
                'address' => $this->address(),
            ];
        }

        if ($this->organizer()) {
            $schema['organizer'] = array_filter([
                '@type' => 'Organization',
                'name' => $this->organizer(),
                'url' => $this->organizerWebsite(),
            ]);
        }

        // Neznáma cena nie je vstup zdarma. API neposkytuje dostupnosť
        // vstupeniek ani začiatok predaja, preto tieto údaje neodhadujeme.
        $amount = $this->data['price_amount'] ?? null;

        if (! $this->isPast() && is_numeric($amount) && is_finite((float) $amount) && (float) $amount >= 0) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => (float) $amount,
                'priceCurrency' => $this->data['price_currency'] ?? 'EUR',
            ];

            // website môže byť iba zdrojový článok, nie predaj vstupeniek.
            if ($this->ticketsEnabled()) {
                $schema['offers']['url'] = $this->portalUrl();
            }
        }

        // performer vyžaduje skutočných účinkujúcich. API ich zatiaľ
        // neposkytuje; canal (organizátor) nie je automaticky účinkujúci.

        return $schema;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->data;
    }
}
