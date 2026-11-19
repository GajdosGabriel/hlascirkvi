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
    public function detect(Post $post, bool $allowAi = true): array
    {
        $found = [];

        if (! DB::table('person_post')->where('post_id', $post->id)->exists()) {
            $title = (string) $post->title;
            $description = $this->description($post);

            foreach ($this->match($title) as $id) {
                $found[$id] = 'title';
            }
            foreach ($this->match($description) as $id) {
                $found[$id] ??= 'description';
            }

            if ($found === [] && ($own = $this->canalPerson($post)) !== null) {
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

        $ids = [];
        foreach ($this->matchers() as $matcher) {
            foreach ($matcher['patterns'] as $pattern) {
                if (preg_match($pattern, $haystack)) {
                    $ids[$matcher['id']] = $matcher['id'];
                    break;
                }
            }
        }

        return array_values($ids);
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

        foreach (DB::table('persons')->whereNull('deleted_at')->get(['id', 'first_name', 'last_name']) as $person) {
            $patterns = [];
            $first = $this->normalize((string) $person->first_name);
            $last = $this->normalize((string) $person->last_name);

            // Meno a priezvisko v oboch poradiach, s koncovkou pádu.
            if (mb_strlen($last) >= 3 && $first !== '') {
                $f = $this->stem(explode(' ', $first)[0]);
                $l = $this->stem($last);
                $patterns[] = '~\b'.$f.'\w{0,4}\s+'.$l.'\w{0,4}\b~u';
                $patterns[] = '~\b'.$l.'\w{0,4}\s+'.$f.'\w{0,4}\b~u';
            }

            // Iné podoby mena presne, ak majú aspoň dve slová.
            foreach ($aliases->get($person->id, []) as $alias) {
                if (str_contains($alias->normalized_name, ' ')) {
                    $patterns[] = '~\b'.preg_quote($alias->normalized_name, '~').'\b~u';
                }
            }

            if ($patterns !== []) {
                $this->matchers[] = ['id' => $person->id, 'patterns' => $patterns];
            }
        }

        return $this->matchers;
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
