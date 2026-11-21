<?php

namespace App\Services\Persons;

use App\Models\AiUsage;
use App\Models\Person;
use App\Models\Post;
use App\Models\Setting;
use App\Services\PostSummarizer;
use App\Support\OpenAiChat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Kto vo videu vystupuje.
 *
 * 1. Pravidlá (zadarmo): meno známej osoby v názve alebo popise, aj
 *    skloňované („Pastirčáka"), v poradí „Priezvisko Meno" a v iných
 *    podobách mena (person_aliases).
 * 2. Osobný kanál: video bez mena dostane osobu, ktorej kanál patrí.
 * 3. AI (len keď je zapnutá a nie je vyčerpaný mesačný limit): z názvu
 *    a popisu vyberie mená rečníkov; neznáma osoba sa založí ako neoverená.
 *
 * Príspevok sa po spracovaní označí persons_checked_at, aby sa neskúšal znova.
 */
class PersonDetector
{
    public const FEATURE = 'person_detect';

    public const SETTING_ENABLED = 'ai_persons.enabled';

    public const CONFIDENCE = ['title' => 90, 'ai' => 75, 'description' => 70, 'canal' => 60];

    /** @var array<int, array{id: int, patterns: array<int, string>}>|null */
    protected ?array $matchers = null;

    /** @var array<string, array<int, int>> koreň priezviska → indexy vzorov */
    protected array $index = [];

    public function __construct(protected PostSummarizer $summarizer) {}

    public function aiEnabled(): bool
    {
        return (bool) Setting::get(self::SETTING_ENABLED, false);
    }

    /**
     * Rozpozná osoby a zapíše väzby. Vráti zdroj každej novej väzby podľa
     * person_id. Príspevok, ktorý už osobu má, sa len označí.
     *
     * @return array<int, string>
     */
    public function detect(Post $post, bool $allowAi = true, bool $allowCanal = true, bool $allowDescription = true): array
    {
        $found = [];

        if (! DB::table('person_post')->where('post_id', $post->id)->exists()) {
            $title = (string) $post->title;
            $description = $allowDescription ? $this->description($post) : '';

            foreach ($this->match($title) as $id) {
                $found[$id] = 'title';
            }
            // „O. Kodet", „vladykom P. Rusnákom", „Pasho Zsolnai": v názve
            // býva len priezvisko s oslovením alebo s prezývkou.
            if ($found === []) {
                foreach ($this->matchSurname($title) as $id) {
                    $found[$id] = 'title';
                }
            }
            foreach ($this->match($description) as $id) {
                $found[$id] ??= 'description';
            }

            if ($found === [] && $allowCanal && ($own = $this->canalPerson($post)) !== null) {
                $found[$own] = 'canal';
            }

            if ($found === [] && $allowAi && $this->canUseAi()) {
                foreach ($this->askAi($post, $title, $description) as $id) {
                    $found[$id] = 'ai';
                }
            }

            $this->store($post, $found);
        }

        DB::table('posts')->where('id', $post->id)->update(['persons_checked_at' => now()]);

        return $found;
    }

    /** Osoby, ktorých meno sa v texte nachádza. @return array<int, int> */
    public function match(string $text): array
    {
        $haystack = ' '.$this->normalize($text).' ';
        if (trim($haystack) === '') {
            return [];
        }

        // Kandidáti podľa koreňa slov v texte — overiť všetky vzory pri
        // každom príspevku by pri tisícoch osôb trvalo príliš dlho.
        $matchers = $this->matchers();
        $candidates = [];
        foreach (array_unique(preg_split('~\W+~u', $haystack, -1, PREG_SPLIT_NO_EMPTY)) as $word) {
            for ($cut = 0; $cut <= 4 && mb_strlen($word) - $cut >= 3; $cut++) {
                foreach ($this->index[$cut ? mb_substr($word, 0, -$cut) : $word] ?? [] as $i) {
                    $candidates[$i] = true;
                }
            }
        }

        $ids = [];
        foreach (array_keys($candidates) as $i) {
            $matcher = $matchers[$i];
            foreach ($matcher['patterns'] as $pattern) {
                if (preg_match($pattern, $haystack)) {
                    $ids[$matcher['id']] = $matcher['id'];
                    break;
                }
            }
        }

        return array_values($ids);
    }

    /**
     * Samotné priezvisko v názve: za oslovením („o.", „vladykom", „pastor")
     * alebo za slovom s veľkým začiatočným písmenom (prezývka, skrátené
     * meno). Platí len pre priezviská, ktoré má jediná osoba a ktoré nie sú
     * zároveň krstným menom ani bežným slovom.
     *
     * @return array<int, int>
     */
    public function matchSurname(string $title): array
    {
        $surnames = $this->surnames();
        if ($surnames === []) {
            return [];
        }

        // Dvojice [slovo, krstné meno pred ním alebo null].
        $words = [];
        $normalized = ' '.$this->normalize($title).' ';
        $prefixes = 'o|otec|otca|otcom|p|pater|patra|patrom|vladyka|vladyku|vladykom|biskup|biskupa|biskupom|arcibiskup|arcibiskupa|mons|kardinal|kardinala|pastor|pastora|pastorom|brat|sestra|dp|vdp|don|fr|kaz';
        if (preg_match_all('~\b(?:'.$prefixes.')\.?\s+(?:\w\.\s*)?(\w{4,})~u', $normalized, $m)) {
            foreach ($m[1] as $word) {
                $words[] = [$word, null];
            }
        }
        if (preg_match_all('~\b(\p{Lu}[\p{L}\'’]*)\.?\s+(\p{Lu}\p{Ll}[\p{Ll}\'’-]{2,})~u', $title, $m, PREG_SET_ORDER)) {
            foreach ($m as $pair) {
                $words[] = [$this->normalize($pair[2]), $this->normalize($pair[1])];
            }
        }

        $ids = [];
        foreach ($words as [$word, $before]) {
            // Skloňované krstné meno („Mikuláša", „Jánovi") nie je priezvisko.
            if ($this->isFirstName($word)) {
                continue;
            }

            // Slovo = koreň priezviska + najviac 4 znaky koncovky pádu.
            $hits = [];
            for ($cut = 0; $cut <= 3 && mb_strlen($word) - $cut >= 4; $cut++) {
                $stem = $cut ? mb_substr($word, 0, -$cut) : $word;
                if (isset($surnames[$stem])) {
                    $hits[$surnames[$stem]] = $this->surnameInfo[$surnames[$stem]];
                }
            }

            $female = (bool) preg_match('~(ov[aeyu]|ovou|ovej|skej|skou)$~u', $word);
            $hits = array_filter($hits, fn ($info) =>
                // Ženský tvar („Samselyová") nepatrí mužovi s tým priezviskom.
                (! $female || $info['female'])
                // Slovo pred priezviskom je krstné meno alebo prezývka a musí
                // sa začínať ako meno osoby: „Pasho" – Pavol, „Evka" – Eva;
                // „Amanda Cook", „Honza Holub" či „Sv. Gorazda" sú iní ľudia.
                && ($before === null || ($info['first'] !== '' && str_starts_with($before, mb_substr($info['first'], 0, 2)))));

            // Slovo pasuje na viac osôb — nič neurčí.
            if (count($hits) === 1) {
                $id = array_key_first($hits);
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    /** @var array<string, int>|null priezvisko (koreň) → osoba */
    protected ?array $surnames = null;

    /** @var array<string, true>|null */
    protected ?array $firstNames = null;

    /** @var array<int, array{first: string, female: bool}> */
    protected array $surnameInfo = [];

    /** @return array<string, int> */
    protected function surnames(): array
    {
        if ($this->surnames !== null) {
            return $this->surnames;
        }

        $ambiguous = array_flip(config('persons.ambiguous_surnames', []));
        $byStem = [];
        foreach (DB::table('persons')->whereNull('deleted_at')->get(['id', 'first_name', 'last_name']) as $person) {
            $last = $this->normalize((string) $person->last_name);
            $this->surnameInfo[$person->id] = [
                'first' => (string) explode(' ', $this->normalize((string) $person->first_name))[0],
                'female' => (bool) preg_match('~(ova|a)$~u', $last),
            ];
            if (mb_strlen($last) < 4 || str_contains($last, ' ') || isset($ambiguous[$last]) || isset($this->firstNames()[$last])) {
                continue;
            }
            $stem = mb_strlen($last) > 4 ? preg_replace('~[aeiouy]$~', '', $last) : $last;
            $byStem[$stem][$person->id] = true;
        }

        // Priezvisko viacerých osôb (Horváth, Tóth…) samo o sebe nič neurčí.
        $this->surnames = [];
        foreach ($byStem as $stem => $ids) {
            if (count($ids) === 1) {
                $this->surnames[$stem] = array_key_first($ids);
            }
        }

        return $this->surnames;
    }

    /** Krstné meno aj v páde: „mikulasa", „janovi" → „mikulas", „jan". */
    protected function isFirstName(string $word): bool
    {
        $names = $this->firstNames();
        for ($cut = 0; $cut <= 3 && mb_strlen($word) - $cut >= 3; $cut++) {
            if (isset($names[$cut ? mb_substr($word, 0, -$cut) : $word])) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, true> */
    protected function firstNames(): array
    {
        return $this->firstNames ??= DB::table('first_names')->where('count', '>=', 30)->pluck('name')
            ->mapWithKeys(fn ($name) => [$this->normalize($name) => true])->all();
    }

    protected function canUseAi(): bool
    {
        return $this->aiEnabled() && $this->summarizer->isConfigured() && ! $this->summarizer->budgetExhausted();
    }

    /**
     * Popis bez odkazov a bez riadkov, ktoré kanál opakuje pod každým videom
     * (pätička s menom kazateľa, poďakovanie, výzva na podporu) — meno
     * z nich nehovorí nič o tomto videu.
     */
    protected function description(Post $post): string
    {
        $lines = array_filter($this->lines((string) $post->body), fn ($line) => ! preg_match('~https?://|www\.|@~u', $line));

        $repeated = [];
        $others = DB::table('posts')->where('canal_id', $post->canal_id)->where('id', '<>', $post->id)
            ->whereNull('deleted_at')->latest('id')->limit(20)->pluck('body');
        foreach ($others as $body) {
            foreach (array_unique($this->lines((string) $body)) as $line) {
                $repeated[$line] = ($repeated[$line] ?? 0) + 1;
            }
        }
        $lines = array_filter($lines, fn ($line) => ($repeated[$line] ?? 0) < 2);

        return mb_substr(implode("\n", $lines), 0, 2000);
    }

    /** @return array<int, string> */
    protected function lines(string $body): array
    {
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $body)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return array_values(array_filter(array_map('trim', preg_split('/\R+/u', $text)), fn ($line) => $line !== ''));
    }

    /** Osoba, ktorej osobný kanál video zverejnil. */
    protected function canalPerson(Post $post): ?int
    {
        $canal = DB::table('canals')->where('id', $post->canal_id)->first(['slug']);
        if (! $canal || in_array($canal->slug, config('persons.canal_default_exclude', []), true)) {
            return null;
        }

        return DB::table('persons')->where('canal_id', $post->canal_id)->whereNull('deleted_at')->value('id');
    }

    /** @return array<int, array{id: int, patterns: array<int, string>}> */
    protected function matchers(): array
    {
        if ($this->matchers !== null) {
            return $this->matchers;
        }

        $aliases = DB::table('person_aliases')->get(['person_id', 'normalized_name'])->groupBy('person_id');
        $this->matchers = [];
        $this->index = [];

        foreach (DB::table('persons')->whereNull('deleted_at')->get(['id', 'first_name', 'last_name']) as $person) {
            $patterns = [];
            $keys = [];
            $first = $this->normalize((string) $person->first_name);
            $last = $this->normalize((string) $person->last_name);

            // Meno a priezvisko v oboch poradiach, s koncovkou pádu.
            if (mb_strlen($last) >= 3 && $first !== '') {
                $f = $this->stem(explode(' ', $first)[0]);
                $l = $this->stem($last);
                $patterns[] = '~\b'.$f.'\w{0,4}\s+'.$l.'\w{0,4}\b~u';
                $patterns[] = '~\b'.$l.'\w{0,4}\s+'.$f.'\w{0,4}\b~u';
                $keys[] = $this->key($last);
            }

            // Iné podoby mena presne, ak majú aspoň dve slová.
            foreach ($aliases->get($person->id, []) as $alias) {
                if (str_contains($alias->normalized_name, ' ')) {
                    $patterns[] = '~\b'.preg_quote($alias->normalized_name, '~').'\b~u';
                    $keys[] = $this->key($alias->normalized_name);
                }
            }

            if ($patterns !== []) {
                $this->matchers[] = ['id' => $person->id, 'patterns' => $patterns];
                foreach (array_unique($keys) as $key) {
                    $this->index[$key][] = array_key_last($this->matchers);
                }
            }
        }

        return $this->matchers;
    }

    /** Kľúč indexu: koreň posledného slova mena (bez úvodzoviek pre regex). */
    protected function key(string $name): string
    {
        $word = (string) collect(explode(' ', $name))->last();

        return mb_strlen($word) > 3 ? preg_replace('~[aeiouy]$~', '', $word) : $word;
    }

    /** Koreň slova bez koncovej samohlásky („kuffa" → „kuff"), aby sedeli aj pády. */
    protected function stem(string $word): string
    {
        $stem = mb_strlen($word) > 3 ? preg_replace('~[aeiouy]$~', '', $word) : $word;

        return preg_quote($stem, '~');
    }

    protected function normalize(string $text): string
    {
        return Person::normalize($text);
    }

    /** @return array<int, int> */
    protected function askAi(Post $post, string $title, string $description): array
    {
        $model = (string) config('openai.summary_model');

        try {
            $response = OpenAiChat::create(OpenAiChat::params($model, 150, 0.0) + [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Z názvu a popisu kresťanského videa urči mená ľudí, ktorí vo videu hovoria: '
                            .'kážu, prednášajú, vydávajú svedectvo alebo sú hosťami rozhovoru. Neuvádzaj ľudí, o ktorých '
                            .'sa len hovorí, svätých, biblické postavy, autorov piesní ani spevákov. Uveď celé meno '
                            .'(meno aj priezvisko), bez titulov. Ak meno v texte nie je, vráť prázdny zoznam. '
                            .'Odpovedz iba JSON v tvare {"speakers": ["Meno Priezvisko"]}.',
                    ],
                    ['role' => 'user', 'content' => "Názov: {$title}\nPopis:\n{$description}"],
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('PersonDetector: AI rozpoznanie zlyhalo', ['post_id' => $post->id, 'error' => $e->getMessage()]);

            return [];
        }

        if ($response->usage) {
            AiUsage::record(self::FEATURE, $post->id, $response->model ?: $model,
                $response->usage->promptTokens, (int) $response->usage->completionTokens);
        }

        $content = (string) ($response->choices[0]->message->content ?? '');
        $data = json_decode(trim(preg_replace('~^```(json)?|```$~m', '', $content)), true);
        $names = is_array($data['speakers'] ?? null) ? $data['speakers'] : [];

        $ids = [];
        foreach (array_slice($names, 0, 4) as $name) {
            $name = is_string($name) ? trim(preg_replace('/\s+/u', ' ', $name)) : '';
            if (count(explode(' ', $name)) < 2 || mb_strlen($name) > 80) {
                continue;
            }
            $ids[] = $this->findOrCreate($name);
        }

        return array_values(array_unique($ids));
    }

    /** Existujúca osoba podľa mena alebo inej podoby mena, inak nová neoverená. */
    protected function findOrCreate(string $name): int
    {
        $normalized = Person::normalize($name);

        $id = DB::table('persons')->where('normalized_name', $normalized)->whereNull('deleted_at')->value('id')
            ?? DB::table('person_aliases')->join('persons', 'persons.id', '=', 'person_aliases.person_id')
                ->where('person_aliases.normalized_name', $normalized)->whereNull('persons.deleted_at')
                ->value('persons.id');
        if ($id !== null) {
            return $id;
        }

        $parts = explode(' ', $name);
        $last = array_pop($parts);
        $slug = Str::slug($name) ?: 'osoba';
        for ($i = 2, $base = $slug; DB::table('persons')->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        $this->matchers = null;

        return Person::create([
            'first_name' => implode(' ', $parts),
            'last_name' => $last,
            'name' => $name,
            'normalized_name' => $normalized,
            'slug' => $slug,
        ])->id;
    }

    /** @param array<int, string> $found */
    protected function store(Post $post, array $found): void
    {
        $now = now();
        $rows = [];
        foreach ($found as $personId => $source) {
            $rows[] = [
                'person_id' => $personId,
                'post_id' => $post->id,
                'source' => $source,
                'matched_text' => null,
                'confidence' => self::CONFIDENCE[$source],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows !== []) {
            DB::table('person_post')->insertOrIgnore($rows);
        }
    }
}
