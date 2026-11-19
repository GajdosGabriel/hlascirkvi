<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Canal;
use App\Models\Person;
use App\Models\PersonAlias;
use App\Models\Post;
use App\Services\Persons\PersonMerger;
use App\Services\Youtube\VideoId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Správa osôb: kto vo videách vystupuje a kde pôsobí. Celá skupina `admin.`
 * beží za middleware `checkSuperAdmin` (routes/web.php).
 */
class PersonController extends Controller
{
    public const FILTERS = [
        'ai' => 'Na kontrolu (AI)',
        'neoverene' => 'Neoverené',
        'overene' => 'Overené',
        'bez-videi' => 'Bez videí',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('filter'), self::FILTERS) ? $request->query('filter') : null;
        $search = mb_substr(trim((string) $request->query('q')), 0, 100);

        $persons = Person::query()
            ->with('canal:id,title')
            ->withCount('posts', 'canals')
            ->when($search !== '', fn ($q) => $q->matching('%'.addcslashes($search, '%_\\').'%'))
            ->when($filter === 'ai', fn ($q) => $q->whereNull('verified_at')
                ->whereExists(fn ($e) => $e->from('person_post')->whereColumn('person_post.person_id', 'persons.id')->where('source', 'ai')))
            ->when($filter === 'neoverene', fn ($q) => $q->whereNull('verified_at'))
            ->when($filter === 'overene', fn ($q) => $q->whereNotNull('verified_at'))
            ->when($filter === 'bez-videi', fn ($q) => $q->doesntHave('posts'))
            ->when($request->query('poradie') === 'nove', fn ($q) => $q->latest('id'), fn ($q) => $q->orderByDesc('posts_count')->orderBy('name'))
            ->paginate(50)
            ->withQueryString();

        return view('admins.persons.index', [
            'persons' => $persons,
            'filter' => $filter,
            'search' => $search,
            'aiCount' => self::aiCount(),
        ]);
    }

    /** Osoby, ktoré založila AI a ešte ich nikto neskontroloval (aj pre úvod administrácie). */
    public static function aiCount(): int
    {
        return Person::query()->whereNull('verified_at')
            ->whereExists(fn ($e) => $e->from('person_post')->whereColumn('person_post.person_id', 'persons.id')->where('source', 'ai'))
            ->count();
    }

    public function create()
    {
        return view('admins.persons.create', ['person' => new Person]);
    }

    public function store(Request $request)
    {
        $person = new Person;
        $this->fill($person, $this->validated($request, $person));
        $person->verified_at = now();
        $person->save();
        $this->syncAliases($person, $request->input('aliases'));

        return redirect()->route('admin.persons.edit', $person)->with('flash', 'Osoba je založená.');
    }

    public function edit(Person $person)
    {
        $person->load(['aliases', 'canal:id,title']);

        return view('admins.persons.edit', [
            'person' => $person,
            'posts' => $person->posts()->without(['favorites', 'images'])->with('canal:id,title')
                ->orderByDesc('posts.id')->paginate(30, ['posts.*'], 'videa')->withQueryString(),
            'canals' => $person->canals()->orderByDesc('canal_person.posts_count')->get(),
            'canalOptions' => Canal::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Person $person)
    {
        $this->fill($person, $this->validated($request, $person));
        $person->verified_at = $request->boolean('verified') ? ($person->verified_at ?? now()) : null;
        $person->save();
        $this->syncAliases($person, $request->input('aliases'));

        return redirect()->route('admin.persons.edit', $person)->with('flash', 'Osoba je uložená.');
    }

    /** Rýchle overenie priamo z výpisu. */
    public function verify(Person $person)
    {
        $person->forceFill(['verified_at' => $person->verified_at ? null : now()])->save();

        return back()->with('flash', $person->verified_at ? 'Osoba je overená.' : 'Overenie je zrušené.');
    }

    public function destroy(Person $person)
    {
        $name = $person->name;
        // Natrvalo: väzby zmaže cudzí kľúč a slug sa uvoľní pre novú osobu.
        $person->forceDelete();

        return redirect()->route('admin.persons.index')->with('flash', "Osoba {$name} je zmazaná aj so svojimi väzbami.");
    }

    public function merge(Request $request, Person $person, PersonMerger $merger)
    {
        $target = $this->personFromInput((string) $request->input('target'));
        if (! $target || $target->is($person)) {
            return back()->withErrors(['target' => 'Vyberte inú existujúcu osobu zo zoznamu.']);
        }

        $merger->merge($person, $target);

        return redirect()->route('admin.persons.edit', $target)
            ->with('flash', "Osoba {$person->name} je zlúčená do {$target->name}.");
    }

    public function attachPost(Request $request, Person $person)
    {
        $post = $this->postFromInput((string) $request->input('post'));
        if (! $post) {
            return back()->withErrors(['post' => 'Video sme nenašli. Zadajte číslo príspevku, jeho adresu alebo odkaz na YouTube.']);
        }

        $this->link($person, $post);

        return back()->with('flash', "Video „{$post->title}“ je priradené.");
    }

    public function detachPost(Person $person, Post $post)
    {
        $person->posts()->detach($post->id);
        DB::table('posts')->where('id', $post->id)->update(['persons_checked_at' => now()]);

        return back()->with('flash', 'Video je odobraté.');
    }

    public function attachCanal(Request $request, Person $person)
    {
        $data = $request->validate([
            'canal_id' => ['required', 'integer', Rule::exists('canals', 'id')],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('canal_person')->updateOrInsert(
            ['canal_id' => $data['canal_id'], 'person_id' => $person->id],
            ['role' => $data['role'] ?: null, 'source' => 'manual', 'updated_at' => now(), 'created_at' => now()],
        );

        return back()->with('flash', 'Kanál je priradený.');
    }

    public function detachCanal(Person $person, Canal $canal)
    {
        $person->canals()->detach($canal->id);

        return back()->with('flash', 'Kanál je odobratý.');
    }

    /** Osoby jedného videa — odkaz „Upraviť osoby" z detailu príspevku. */
    public function post(Post $post)
    {
        return view('admins.persons.post', [
            'post' => $post,
            'persons' => $post->persons()->orderBy('name')->get(),
        ]);
    }

    public function attachToPost(Request $request, Post $post)
    {
        $person = $this->personFromInput((string) $request->input('person'));
        if (! $person) {
            $name = trim(preg_replace('/\s+/u', ' ', (string) $request->input('person')));
            if (! $request->boolean('create') || count(explode(' ', $name)) < 2) {
                return back()->withInput()->withErrors(['person' => 'Osobu sme nenašli. Vyberte ju zo zoznamu alebo zaškrtnite „Založiť novú osobu“.']);
            }
            $person = new Person;
            $parts = explode(' ', mb_substr($name, 0, 120));
            $this->fill($person, ['last_name' => array_pop($parts), 'first_name' => implode(' ', $parts)]);
            $person->verified_at = now();
            $person->save();
        }

        $this->link($person, $post);

        return back()->with('flash', "{$person->name} je priradený k videu.");
    }

    public function detachFromPost(Post $post, Person $person)
    {
        $post->persons()->detach($person->id);
        DB::table('posts')->where('id', $post->id)->update(['persons_checked_at' => now()]);

        return back()->with('flash', 'Osoba je odobratá.');
    }

    protected function link(Person $person, Post $post): void
    {
        DB::table('person_post')->updateOrInsert(
            ['person_id' => $person->id, 'post_id' => $post->id],
            ['source' => 'manual', 'confidence' => 100, 'updated_at' => now(), 'created_at' => now()],
        );
        DB::table('posts')->where('id', $post->id)->update(['persons_checked_at' => now()]);
    }

    protected function validated(Request $request, Person $person): array
    {
        return $request->validate([
            'prefix' => ['nullable', 'string', 'max:60'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'title_suffix' => ['nullable', 'string', 'max:60'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('persons', 'slug')->ignore($person->id)],
            'canal_id' => ['nullable', 'integer', Rule::exists('canals', 'id')],
            'description' => ['nullable', 'string', 'max:255'],
            'aliases' => ['nullable', 'string', 'max:3000'],
        ], ['slug.regex' => 'Adresa môže obsahovať len malé písmená bez diakritiky, číslice a pomlčky.']);
    }

    /** Meno sa skladá z mena a priezviska; tituly stoja mimo, aby sa podľa nich nehľadalo. */
    protected function fill(Person $person, array $data): void
    {
        $person->fill(collect($data)->except(['aliases', 'slug'])->all());
        $person->name = trim(($person->first_name ?? '').' '.$person->last_name);
        $person->normalized_name = Person::normalize($person->name);

        $slug = $data['slug'] ?? null ?: ($person->slug ?: Str::slug($person->name));
        for ($i = 2, $base = $slug; Person::withTrashed()->where('slug', $slug)->whereKeyNot($person->id)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }
        $person->slug = $slug;
    }

    /** Iné podoby mena, jedna na riadok. */
    protected function syncAliases(Person $person, ?string $input): void
    {
        $names = collect(preg_split('/\R+/u', (string) $input))
            ->map(fn ($name) => trim(preg_replace('/\s+/u', ' ', $name)))
            ->filter(fn ($name) => $name !== '' && Person::normalize($name) !== $person->normalized_name)
            ->unique(fn ($name) => Person::normalize($name));

        $person->aliases()->whereNotIn('normalized_name', $names->map(fn ($name) => Person::normalize($name))->all())->delete();
        foreach ($names as $name) {
            PersonAlias::firstOrCreate(['person_id' => $person->id, 'normalized_name' => Person::normalize($name)], ['name' => mb_substr($name, 0, 255)]);
        }
    }

    /** „Meno Priezvisko (#123)" zo zoznamu, alebo presné meno. */
    protected function personFromInput(string $value): ?Person
    {
        if (preg_match('/#(\d+)\)?\s*$/', $value, $m)) {
            return Person::find($m[1]);
        }
        $value = trim($value);

        return $value === '' ? null : Person::where('normalized_name', Person::normalize($value))->first();
    }

    /** Číslo príspevku, adresa príspevku na webe, alebo odkaz či ID videa na YouTube. */
    protected function postFromInput(string $value): ?Post
    {
        $value = trim($value);
        if (preg_match('~/post/(\d+)~', $value, $m) || ctype_digit($value)) {
            return Post::find($m[1] ?? $value);
        }
        $videoId = VideoId::fromInput($value);

        return $videoId ? Post::where('video_id', $videoId)->first() : null;
    }
}
