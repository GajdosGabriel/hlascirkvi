<?php

namespace App\View\Components\Dashboard;

use App\Models\Organization;
use App\Services\Dashboard\DashboardStats;
use Illuminate\View\Component;

/**
 * Spoločný rám správcovských stránok kanála.
 *
 * Hlavičku s identitou kanála a záložkami niesla len nástenka
 * (resources/views/dashboard/index.blade.php), ostatné sekcie profilu bežali
 * na starom x-pages.dashboard s bočným menu. Kliknutie zo záložky „Články“ tak
 * viedlo na stránku, ktorá vyzerala ako z iného webu. Rám je preto tu, jeden
 * pre všetky sekcie, a stránka doňho vkladá už len svoj obsah:
 *
 *     <x-dashboard.shell :organization="$organization" section="posts" heading="Články">
 *         <x-slot name="actions"> ... </x-slot>
 *         ... obsah ...
 *     </x-dashboard.shell>
 *
 * Stránka musí zapnúť aj vzhľad: @section('body-class', 'ar-body') a
 * @include('partials.dashboard-head') v sekcii headerCSS — layout ich
 * nenačítava globálne, staršie stránky ostávajú na pôvodnom podklade.
 */
class Shell extends Component
{
    /** Záložky sekcií v poradí, v akom stoja v hlavičke. */
    public array $tabs;

    /**
     * @param  string       $section  kľúč zvýraznenej záložky (viď tabs())
     * @param  string|null  $heading  nadpis stránky; nástenka nesie meno kanála
     * @param  array|null   $counts   ['posts' => n, 'seminars' => n, 'prayers' => n];
     *                                nástenka ich má už spočítané zo súhrnu,
     *                                ostatné stránky si ich nechajú dopočítať
     */
    public function __construct(
        public Organization $organization,
        public string $section = 'dashboard',
        public ?string $heading = null,
        ?array $counts = null,
    ) {
        $this->heading = $heading ?: $organization->title;
        $this->tabs = $this->tabs($counts ?? app(DashboardStats::class)->tabCounts($organization));
    }

    /**
     * Popis záložky, ktorá je práve otvorená — omrvinková navigácia z neho
     * berie posledný článok cesty.
     */
    public function current(): ?array
    {
        return $this->tabs[$this->section] ?? null;
    }

    public function render()
    {
        return view('components.dashboard.shell');
    }

    /**
     * Čísla pri záložkách sú zámerne len tam, kde niečo hovoria: pri kanáloch
     * a kontaktoch by šlo o ďalšie dopyty za údaj, ktorý správcu nezaujíma.
     */
    protected function tabs(array $counts): array
    {
        $id   = $this->organization->id;
        $user = auth()->id();

        return [
            'dashboard' => [
                'url'   => route('profile.dashboard'),
                'icon'  => 'fas fa-th-large',
                'label' => 'Nástenka',
            ],
            'posts' => [
                'url'   => route('profile.organization.post.index', $id),
                'icon'  => 'far fa-newspaper',
                'label' => 'Články',
                'count' => $counts['posts'] ?? null,
            ],
            'seminars' => [
                'url'   => route('profile.organization.seminar.index', $id),
                'icon'  => 'fas fa-graduation-cap',
                'label' => 'Semináre',
                'count' => $counts['seminars'] ?? null,
            ],
            'prayers' => [
                'url'   => route('profile.organization.prayer.index', $id),
                'icon'  => 'fas fa-pray',
                'label' => 'Modlitby',
                'count' => $counts['prayers'] ?? null,
            ],
            'organizations' => [
                'url'   => route('profile.user.organization.index', $user),
                'icon'  => 'fas fa-broadcast-tower',
                'label' => 'Kanály',
            ],
            'addresses' => [
                'url'   => route('profile.user.address.index', $user),
                'icon'  => 'far fa-address-book',
                'label' => 'Kontakty',
            ],
        ];
    }
}
