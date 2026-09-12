<?php

/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 24.08.2018
 * Time: 7:51
 */

namespace App\Services;

use App\Models\BufferPublication;
use App\Models\Post;
use App\Notifications\Admin\BufeerIsEmpty;
use App\Repositories\Contracts\PostRepository;
use App\Repositories\Contracts\UserRepository;
use App\Services\Buffer\PublishPlan;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

/**
 * Vypúšťa príspevky z buffera po jednom počas dňa.
 *
 * Príkaz beží často (každých pár minút), ale zverejní len vtedy, keď dozrel
 * najbližší slot z denného plánu — pozri App\Services\Buffer\PublishPlan
 * a config/buffer.php. Väčšina behov teda skončí bez zásahu a v zozname
 * pribúda po jednom v nepravidelných časoch.
 */
class Buffer
{
    protected $post;

    protected PublishPlan $plan;

    public function __construct(PostRepository $postRepository, PublishPlan $plan)
    {
        $this->post = $postRepository;
        $this->plan = $plan;
    }

    /**
     * Jeden beh publishera. Vráti zverejnený príspevok, alebo null, ak sa
     * (zatiaľ) nemá čo diať.
     */
    public function handler(bool $force = false): ?Post
    {
        $now = CarbonImmutable::now();
        $status = $this->status($now);

        if ($status['waiting'] === 0) {
            return null;
        }

        if (! $force && ! $this->isTime($status, $now)) {
            return null;
        }

        $choice = $this->pickPost($now, $status);

        if ($choice === null) {
            return null;
        }

        return $this->publish(
            $choice['post'],
            $this->publishedAt($now, $force ? null : $status['next_slot']),
            $choice['archive']
        );
    }

    /**
     * Stav pre dnešok — pre konzolu aj pre admin výpis buffera.
     *
     * @return array{waiting:int, done_today:int, archive_today:int, inflow:float,
     *               plan:list<CarbonImmutable>, next_slot:?CarbonImmutable,
     *               last_publication:?CarbonImmutable}
     */
    public function status(?CarbonImmutable $now = null): array
    {
        $now = $now ?: CarbonImmutable::now();

        $waiting = $this->post->countWaitingPosts();
        $today = BufferPublication::onDate($now)->orderBy('slot_at')->get();
        $doneToday = $today->count();

        // Plán sa počíta z frontu na začiatku dňa (čakajúce + dnes vydané),
        // inak by sa počas dňa skracoval sám sebe pod rukami.
        $inflow = $this->inflow($now);
        $plan = $this->plan->forDate($now, $waiting + $doneToday, $inflow);

        return [
            'waiting' => $waiting,
            'done_today' => $doneToday,
            'archive_today' => $today->where('archive', true)->count(),
            'inflow' => $inflow,
            'plan' => $plan,
            'next_slot' => $plan[$doneToday] ?? null,
            'last_publication' => $today->last()?->created_at
                ? CarbonImmutable::instance($today->last()->created_at)
                : null,
        ];
    }

    /**
     * Denný prítok do buffera — čo v ňom za posledné dni pribudlo, plus čo
     * z neho medzitým vyšlo. Publisher podľa toho drží tempo s importom,
     * aby front nerástol donekonečna.
     */
    protected function inflow(CarbonImmutable $now): float
    {
        $days = max(1, (int) config('buffer.daily.inflow_days'));
        $since = $now->subDays($days);

        // Rozhodujú príchody, nie odchody: čo v okne pribudlo a ešte čaká, plus
        // čo v ňom pribudlo a medzitým už vyšlo. Keby sa zverejnenia rátali
        // podľa dňa vydania, rozpúšťanie archívu by si nafúklo prítok samo
        // sebou a v dennej kvóte by archívu neostal ani slot.
        $arrived = $this->post->countWaitingPostsSince($since)
            + BufferPublication::where('arrived_at', '>=', $since)->count();

        return $arrived / $days;
    }

    /**
     * Dozrel slot? Okrem plánu drží aj minimálny odstup — po výpadku cronu sa
     * tak zmeškané sloty rozpustia postupne, nevyjdú naraz.
     */
    protected function isTime(array $status, CarbonImmutable $now): bool
    {
        if ($status['next_slot'] === null || $now->lt($status['next_slot'])) {
            return false;
        }

        $gap = (int) config('buffer.min_gap_minutes');

        return $status['last_publication'] === null
            || $status['last_publication']->lte($now->subMinutes($gap));
    }

    /**
     * Kto je na rade — a či sa siaha po čerstvom príspevku, alebo do archívu.
     *
     * Denná kvóta sa skladá z prítoku a z rozpúšťania starého frontu, takže
     * rovnako sa delia aj sloty: väčšina patrí čerstvým videám, zvyšok archívu,
     * rovnomerne rozsypaný pomedzi ne. Keď na jednu z tých dvoch kôp nič
     * nezostane, slot prevezme tá druhá — nech deň nezostane prázdny.
     *
     * @return array{post: Post, archive: bool}|null
     */
    protected function pickPost(CarbonImmutable $now, array $status): ?array
    {
        $freshSince = $now->subDays((int) config('buffer.archive_after_days'));
        $organizations = $this->post->waitingOrganizations($freshSince);

        $order = $this->archiveTurn($status) ? ['archive', 'fresh'] : ['fresh', 'archive'];

        foreach ($order as $kind) {
            $post = $kind === 'archive'
                ? $this->fromArchive($organizations, $now)
                : $this->fromFresh($organizations, $now, $freshSince);

            if ($post !== null) {
                return ['post' => $post, 'archive' => $kind === 'archive'];
            }
        }

        return null;
    }

    /**
     * Pripadol tento slot archívu? Archívnych slotov je za deň toľko, o koľko
     * kvóta presahuje denný prítok, a rozdelia sa po dni rovnomerne.
     */
    protected function archiveTurn(array $status): bool
    {
        $quota = max(1, count($status['plan']));
        $archive = max(0, $quota - (int) ceil($status['inflow']));

        if ($archive === 0) {
            return false;
        }

        $done = $status['done_today'];

        return (int) floor(($done + 1) * $archive / $quota) > (int) floor($done * $archive / $quota);
    }

    /**
     * Čerstvý príspevok. Na rade je kanál, z ktorého sa najdlhšie nič
     * neukázalo, pri zhode ten s najstarším čakajúcim príspevkom.
     */
    protected function fromFresh($organizations, CarbonImmutable $now, CarbonImmutable $freshSince): ?Post
    {
        $candidates = $this->underDailyLimit(
            $organizations->filter(fn ($organization) => $organization->fresh_count > 0),
            $now
        )->sortBy([
            // null = z kanála ešte nikdy nič nevyšlo, ide prvý
            fn ($a, $b) => ($a->last_published ?? '') <=> ($b->last_published ?? ''),
            fn ($a, $b) => $a->oldest_waiting <=> $b->oldest_waiting,
        ]);

        $organization = $this->withoutLastOrganization($candidates, $now)->first();

        return $organization
            ? $this->post->nextWaitingPost($organization->organization_id, $freshSince)
            : null;
    }

    /**
     * Kúsok starého frontu — vždy ten úplne najstarší, ktorý ešte čaká.
     */
    protected function fromArchive($organizations, CarbonImmutable $now): ?Post
    {
        $organization = $this->underDailyLimit(
            $organizations->filter(fn ($organization) => $organization->waiting_count > $organization->fresh_count),
            $now
        )->sortBy(fn ($organization) => $organization->oldest_waiting)->first();

        return $organization
            ? $this->post->nextWaitingPost($organization->organization_id)
            : null;
    }

    /**
     * Kanály, ktoré dnes ešte nevyčerpali svoj strop. Ak čaká jediný kanál,
     * strop sa neuplatní — nie je čo striedať a deň by inak stíchol.
     */
    protected function underDailyLimit($organizations, CarbonImmutable $now)
    {
        if ($organizations->count() < 2) {
            return $organizations->values();
        }

        $limit = (int) config('buffer.max_per_organization');

        $publishedToday = BufferPublication::onDate($now)
            ->selectRaw('organization_id, count(*) as pocet')
            ->groupBy('organization_id')
            ->pluck('pocet', 'organization_id');

        return $organizations
            ->filter(fn ($organization) => ($publishedToday[$organization->organization_id] ?? 0) < $limit)
            ->values();
    }

    /**
     * Dva príspevky z toho istého kanála za sebou prezradia automat.
     */
    protected function withoutLastOrganization($organizations, CarbonImmutable $now)
    {
        if ($organizations->count() < 2) {
            return $organizations->values();
        }

        $last = BufferPublication::onDate($now)->orderByDesc('id')->value('organization_id');

        return $organizations
            ->reject(fn ($organization) => $organization->organization_id == $last)
            ->values();
    }

    /**
     * Čas, ktorý sa zapíše príspevku. Slot má nepravidelnú minútu aj sekundu
     * (napr. 9:23:41), zatiaľ čo cron beží po piatich minútach — ak sa slot
     * stihol, použije sa on. Pri dobiehaní staršieho slotu by bol príspevok
     * datovaný spätne, tam sa použije aktuálny čas.
     */
    protected function publishedAt(CarbonImmutable $now, ?CarbonImmutable $slot): CarbonImmutable
    {
        if ($slot !== null && $slot->between($now->subMinutes(20), $now)) {
            return $slot;
        }

        return $now;
    }

    protected function publish(Post $post, CarbonImmutable $at, bool $archive = false): Post
    {
        // Zverejnenie prepíše created_at časom vydania, tak si čas importu
        // odložíme skôr, než sa stratí.
        $arrivedAt = $post->created_at;

        return DB::transaction(function () use ($post, $at, $archive, $arrivedAt) {
            $this->post->publishPost($post, $at);

            BufferPublication::create([
                'post_id' => $post->id,
                'organization_id' => $post->organization_id,
                'slot_at' => $at,
                'archive' => $archive,
                'arrived_at' => $arrivedAt,
            ]);

            return $post;
        });
    }

    public function ifBufferIsEmpty(UserRepository $userRepository)
    {
        Notification::send($userRepository->usersHasRoleAdmin(), new BufeerIsEmpty());
    }
}
