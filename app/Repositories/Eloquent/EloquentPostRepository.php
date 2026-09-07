<?php
/**
 * Created by PhpStorm.
 * User: Gabriel
 * Date: 16.11.2018
 * Time: 20:18
 */

namespace App\Repositories\Eloquent;

use App\Models\Post;
use Carbon\Carbon;
use App\Repositories\AbstractRepository;
use App\Repositories\Contracts\PostRepository;
use CyrildeWit\EloquentViewable\Support\Period;

class EloquentPostRepository extends AbstractRepository implements PostRepository
{
    public function entity()
    {
        return Post::class;
    }


    /**
     * Príspevky updatera zoskupené po kanáloch. Výpisy z každého kanála ukazujú
     * len prvých pár položiek, preto sa načíta rovno len toľko riadkov —
     * pôvodné get()->groupBy() ťahalo do pamäte celú históriu (pri nedeľných
     * prenosoch vyše 9 000 príspevkov aj s obrázkami) a zvyšok zahodilo.
     */
    public function getPostsByUpdater($idUpdaters, $perOrganization = 5)
    {
        $ids = $this->latestIdsPerOrganization($idUpdaters, $perOrganization);

        if (empty($ids)) {
            return collect();
        }

        return $this->entity->whereIn('posts.id', $ids)
            ->orderBy('id', 'desc')->get()->groupBy('organization_id');
    }

    /**
     * Id najnovších príspevkov updatera, najviac $perOrganization na kanál.
     * Okenná funkcia to zvládne jedným prechodom cez index, bez triedenia
     * v PHP.
     */
    protected function latestIdsPerOrganization($idUpdaters, $perOrganization)
    {
        $ranked = \DB::table('posts')
            ->join('post_updater', 'posts.id', '=', 'post_updater.post_id')
            ->where('post_updater.updater_id', $idUpdaters)
            ->whereNull('posts.deleted_at')
            ->whereNull('posts.video_available')
            ->where('posts.youtube_blocked', 0)
            ->select('posts.id')
            ->selectRaw('row_number() over (partition by posts.organization_id order by posts.id desc) as poradie');

        return \DB::query()->fromSub($ranked, 'zoradene')
            ->where('poradie', '<=', $perOrganization)
            ->pluck('id')
            ->all();
    }


    public function postsByUpdater($idUpdaters)
    {
        return $this->entity->whereHas('updaters', function ($query) use ($idUpdaters) {
            $query->whereId($idUpdaters);
        })->where('video_available', NULL);
    }

    public function postsByTag($idTag)
    {
        return $this->entity->whereHas('tags', function ($query) use ($idTag) {
            $query->whereId($idTag);
        });
    }


    protected function unpublished()
    {
        return $this->entity->doesntHave('updaters');
    }

    public function unpublishedPaginate($perPage)
    {
        return $this->unpublished()->paginate($perPage);
    }

    // For buffer ---------------

    /**
     * Čakajúce príspevky: bez updatera (teda nezverejnené), s dostupným videom.
     *
     * Zámerne cez query builder — Post má $with (favorites, images,
     * organization) aj $appends, takže načítanie modelov len kvôli počtu by
     * spustilo niekoľko dopytov na každý riadok.
     */
    protected function waitingPostsQuery()
    {
        return \DB::table('posts')
            ->whereNull('posts.deleted_at')
            ->where('posts.youtube_blocked', 0)
            ->whereNull('posts.video_available')
            ->whereNotExists(function ($query) {
                $query->select(\DB::raw(1))
                    ->from('post_updater')
                    ->whereColumn('post_updater.post_id', 'posts.id');
            });
    }

    public function countWaitingPosts()
    {
        return $this->waitingPostsQuery()->count();
    }

    /**
     * Koľko čakajúcich príspevkov pribudlo od zadaného času. Spolu so
     * záznamami v `buffer_publications` z toho vyjde denný prítok, podľa
     * ktorého si publisher nastaví tempo.
     */
    public function countWaitingPostsSince($since)
    {
        return $this->waitingPostsQuery()->where('posts.created_at', '>=', $since)->count();
    }

    /**
     * Kanály s čakajúcimi príspevkami — počet, vek najstaršieho čakajúceho
     * a čas, kedy z kanála naposledy niečo vyšlo. Z toho si publisher vyberá,
     * kto je na rade.
     */
    public function waitingOrganizations($idUpdater, $freshSince = null)
    {
        $organizations = $this->waitingPostsQuery()
            ->groupBy('posts.organization_id')
            ->select('posts.organization_id')
            ->selectRaw('count(*) as waiting_count')
            ->selectRaw('min(posts.created_at) as oldest_waiting')
            // Koľko z nich je čerstvých; zvyšok je archív. Podmienený súčet to
            // zvládne v tom istom prechode, netreba druhý dopyt.
            ->selectRaw('sum(case when posts.created_at >= ? then 1 else 0 end) as fresh_count', [$freshSince ?: '1970-01-01'])
            ->get();

        if ($organizations->isEmpty()) {
            return $organizations;
        }

        $lastPublished = \DB::table('posts')
            ->join('post_updater', 'posts.id', '=', 'post_updater.post_id')
            ->where('post_updater.updater_id', $idUpdater)
            ->whereNull('posts.deleted_at')
            ->whereIn('posts.organization_id', $organizations->pluck('organization_id'))
            ->groupBy('posts.organization_id')
            ->selectRaw('posts.organization_id, max(posts.created_at) as last_published')
            ->pluck('last_published', 'posts.organization_id');

        return $organizations->map(function ($organization) use ($lastPublished) {
            $organization->last_published = $lastPublished[$organization->organization_id] ?? null;

            return $organization;
        });
    }

    /**
     * Najstarší čakajúci príspevok kanála. V rámci kanála sa ide vždy od
     * najstaršieho — pri výbere najnovšieho (pôvodné správanie) sa staršie
     * príspevky nedostali na rad nikdy a ostávali v bufferi aj roky.
     *
     * $freshSince ohraničí výber na čerstvé príspevky, aby nové videá nečakali
     * za archívom; archív si publisher berie samostatnými slotmi.
     */
    public function nextWaitingPost($organizationId, $freshSince = null)
    {
        return $this->entity->whereOrganizationId($organizationId)
            ->doesntHave('updaters')
            ->whereNull('video_available')
            ->when($freshSince, fn ($query) => $query->where('created_at', '>=', $freshSince))
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();
    }

    /*
     * Publishing manually For adminController
     */
    public function findAndPublishPost($post, $IdUpdater)
    {
        $this->publishPost($this->entity->find($post), $IdUpdater);
    }

    /*
     * Published for buffer
     */
    public function publishPost($post, $IdUpdater, $publishedAt = null)
    {
        $publishedAt = $publishedAt ?: now();

        // Priame priradenie, nie update() — `created_at` nie je vo $fillable,
        // takže cez hromadné plnenie ticho vypadol a príspevok ostal vo výpise
        // datovaný dňom importu. Práve podľa `created_at` sa radí predný
        // zoznam, čiže staršie príspevky sa po zverejnení nikde neukázali.
        $post->created_at = $publishedAt;
        $post->published = $publishedAt;
        $post->save();

        $post->updaters()->attach($IdUpdater);

        return $post;
    }


    public function getPostsByDatetime()
    {
        return $this->entity
            ->whereYear('created_at', '=', date('Y'))
            ->whereMonth('created_at', '=', date('m'))
            ->whereDay('created_at', '=', date('d'))
            ->get();
    }

    // For buffer ---------------



    public function countUnwatchedSundayServicesVideos()
    {
        // Beží v SetCache, čiže na každej požiadavke. Cez model sa na dopyt
        // dostane globálny scope, soft delete aj video_available — a tým aj
        // index posts_feed_created_index, ktorý rozsah created_at prejde bez
        // toho, aby sa dotýkal celej spojovacej tabuľky.
        $unwatchedVideos = $this->postsByUpdater(16)
            ->where('created_at', '>', session()->get('lastVisit'))
            ->count();

        if ($unwatchedVideos > 0) {
            session()->put('countUnwatchedVideos', $unwatchedVideos);
        }
    }

    /**
     * Archív kanála pre vodorovný pás na detaile príspevku. Keysetové
     * stránkovanie drží dopyt na indexe posts_organization_created_index aj
     * pri desiatej dávke — offset by sa s každým "ďalej" znovu prehrýzal cez
     * všetky predošlé riadky. Id je v zoradení ako rozhodca: importované
     * príspevky zdieľajú created_at do sekundy a bez neho by kurzor riadky
     * preskakoval.
     */
    public function organizationRail($organizationId, $exceptId, $perPage = 6)
    {
        return $this->entity->whereOrganizationId($organizationId)
            ->whereKeyNot($exceptId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);
    }

    public function countInOrganization($organizationId)
    {
        return $this->entity->whereOrganizationId($organizationId)->count();
    }

    /**
     * Najsledovanejšie v kanáli. Radí sa podľa stĺpca count_view, nie cez
     * eloquent-viewable — ten skladá počty zo samostatnej tabuľky návštev
     * a pre bočný panel by to bol dopyt nad státisícami riadkov navyše.
     */
    public function mostViewedInOrganization($organizationId, $exceptId = null, $limit = 5)
    {
        return $this->entity->whereOrganizationId($organizationId)
            // Profil kanála panel vykresľuje bez toho, aby stál na konkrétnom
            // príspevku. whereKeyNot(null) by sa preložilo na `id <> null`,
            // teda podmienku, ktorú nesplní ani jeden riadok.
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->where('count_view', '>', 0)
            ->orderBy('count_view', 'desc')
            ->limit($limit)
            ->get();
    }

    public function firstInOrganization($organizationId, $exceptId)
    {
        return $this->entity->whereOrganizationId($organizationId)
            ->whereKeyNot($exceptId)
            ->oldest()
            ->first();
    }

    /**
     * Najbližší starší príspevok k danému okamihu — "pred rokom" v archíve.
     */
    public function inOrganizationBefore($organizationId, $exceptId, $moment)
    {
        return $this->entity->whereOrganizationId($organizationId)
            ->whereKeyNot($exceptId)
            ->where('created_at', '<=', $moment)
            ->latest()
            ->first();
    }



    /**
     * Súhrn kanála do hlavičky profilu: koľko toho vydal, koľkokrát to niekto
     * otvoril a odkedy pokedy siaha archív. Jeden prechod cez
     * posts_organization_created_index namiesto štyroch samostatných dopytov.
     *
     * Zámerne cez query builder — Post má $with aj $appends, takže načítanie
     * modelov len kvôli súčtu by spustilo niekoľko dopytov na každý riadok.
     */
    public function organizationSummary($organizationId)
    {
        return \DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->selectRaw('count(*) as posts_count')
            ->selectRaw('coalesce(sum(count_view), 0) as views_sum')
            ->selectRaw('min(created_at) as first_at')
            ->selectRaw('max(created_at) as last_at')
            ->first();
    }

    /**
     * Mesačný rozpis archívu kanála pre navigátor v bočnom paneli. Vracia
     * riadky rok/mesiac/počet; mesiace bez príspevku v ňom nie sú, prázdne
     * políčka mriežky si doplní pohľad.
     */
    public function organizationArchive($organizationId)
    {
        return \DB::table('posts')
            ->where('organization_id', $organizationId)
            ->where('youtube_blocked', 0)
            ->whereNull('deleted_at')
            ->selectRaw('year(created_at) as rok, month(created_at) as mesiac, count(*) as pocet')
            ->groupBy('rok', 'mesiac')
            ->orderBy('rok')
            ->orderBy('mesiac')
            ->get();
    }

    /**
     * Posledné komentáre pod príspevkami kanála. Komentáre visia na príspevku
     * polymorfne a stĺpec s kanálom tabuľka `comments` nemá, preto sa ide
     * joinom cez `posts`.
     *
     * Vracia holé riadky: cez model by si každý komentár dotiahol celý
     * príspevok aj s obrázkami, kanálom a obľúbenými, hoci panel z neho
     * potrebuje len titulok a slug. Meno autora nesie stĺpec `user_name` —
     * komentáre z importu vlastného užívateľa nemajú.
     */
    public function latestCommentsInOrganization($organizationId, $limit = 6)
    {
        return $this->organizationCommentsQuery($organizationId)
            ->orderBy('comments.created_at', 'desc')
            ->limit($limit)
            ->select([
                'comments.id',
                'comments.body',
                'comments.created_at',
                'comments.user_name',
                'posts.id as post_id',
                'posts.title as post_title',
                'posts.slug as post_slug',
            ])
            ->get();
    }

    public function countCommentsInOrganization($organizationId)
    {
        return $this->organizationCommentsQuery($organizationId)->count();
    }

    protected function organizationCommentsQuery($organizationId)
    {
        return \DB::table('comments')
            ->join('posts', 'posts.id', '=', 'comments.commentable_id')
            ->where('comments.commentable_type', Post::class)
            ->where('posts.organization_id', $organizationId)
            ->where('posts.youtube_blocked', 0)
            ->whereNull('comments.deleted_at')
            ->whereNull('posts.deleted_at');
    }


    /*
     *  Najsledovanejšie videa
     */
    public function newlleterMostVisited()
    {
        return $this->entity->where('created_at', '>', Carbon::now()->subDays(30))
        ->orderByViews('desc', Period::pastDays(30));
    }
}
