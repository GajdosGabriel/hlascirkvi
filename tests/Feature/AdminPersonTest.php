<?php

namespace Tests\Feature;

use App\Models\Canal;
use App\Models\Person;
use App\Models\PersonAlias;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPersonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(['admin', 'superadmin']);

        return $admin;
    }

    private function person(string $name, array $attributes = []): Person
    {
        [$first, $last] = explode(' ', $name, 2);

        return Person::create($attributes + [
            'first_name' => $first,
            'last_name' => $last,
            'name' => $name,
            'normalized_name' => Person::normalize($name),
            'slug' => \Illuminate\Support\Str::slug($name),
        ]);
    }

    public function test_bezny_pouzivatel_sa_do_spravy_osob_nedostane(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/osoby')->assertRedirect('/');
    }

    public function test_zoznam_hlada_a_filtruje_osoby_z_ai(): void
    {
        $ai = $this->person('Peter Novák');
        $ai->posts()->attach(Post::factory()->create()->id, ['source' => 'ai', 'confidence' => 75]);
        $this->person('Ján Buc', ['verified_at' => now()]);

        $this->actingAs($this->admin())->get('/admin/osoby?filter=ai')
            ->assertOk()->assertSee('Peter Novák')->assertDontSee('Ján Buc');
        $this->get('/admin/osoby?q=buc')->assertOk()->assertSee('Ján Buc')->assertDontSee('Peter Novák');
        $this->get('/admin/home')->assertOk()->assertSee('osôb založila AI na kontrolu');
    }

    public function test_uprava_mena_adresy_a_inych_podob(): void
    {
        $person = $this->person('Daniel Pastircak');

        $this->actingAs($this->admin())->put(route('admin.persons.update', $person), [
            'first_name' => 'Daniel',
            'last_name' => 'Pastirčák',
            'slug' => '',
            'description' => 'kazateľ Cirkevného zboru Kaplnka',
            'aliases' => "Pastýrčák\nDaniel Pastircak\nDaniel Pastirčák",
            'verified' => '1',
        ])->assertRedirect();

        $person->refresh();
        $this->assertSame('Daniel Pastirčák', $person->name);
        $this->assertSame('daniel pastircak', $person->normalized_name);
        $this->assertNotNull($person->verified_at);
        // Zhodné s menom sa medzi iné podoby nezapíše.
        $this->assertSame(['Pastýrčák'], $person->aliases()->pluck('name')->all());
        $this->get('/osoba/'.$person->slug)->assertSee('kazateľ Cirkevného zboru Kaplnka');
    }

    public function test_priradenie_a_odobratie_videa_a_kanala(): void
    {
        $person = $this->person('Erik Matej');
        $post = Post::factory()->create(['video_id' => 'abcdefghijk']);
        $canal = Canal::factory()->create();
        $this->actingAs($this->admin());

        $this->post(route('admin.persons.posts.attach', $person), ['post' => 'https://www.youtube.com/watch?v=abcdefghijk'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('person_post', ['person_id' => $person->id, 'post_id' => $post->id, 'source' => 'manual']);

        $this->post(route('admin.persons.posts.attach', $person), ['post' => 'neexistuje'])->assertSessionHasErrors('post');

        $this->delete(route('admin.persons.posts.detach', [$person, $post->id]))->assertRedirect();
        $this->assertDatabaseMissing('person_post', ['person_id' => $person->id]);

        $this->post(route('admin.persons.canals.attach', $person), ['canal_id' => $canal->id, 'role' => 'pastor']);
        $this->assertDatabaseHas('canal_person', ['person_id' => $person->id, 'canal_id' => $canal->id, 'role' => 'pastor', 'source' => 'manual']);

        $this->delete(route('admin.persons.canals.detach', [$person, $canal->id]));
        $this->assertDatabaseMissing('canal_person', ['person_id' => $person->id]);
    }

    public function test_zlucenie_presunie_videa_a_meno_ostane_ako_ina_podoba(): void
    {
        $dup = $this->person('Peter Beň');
        $real = $this->person('Peter Beňo');
        $post = Post::factory()->create();
        $dup->posts()->attach($post->id, ['source' => 'title', 'confidence' => 90]);

        $this->actingAs($this->admin())
            ->post(route('admin.persons.merge', $dup), ['target' => "Peter Beňo (#{$real->id})"])
            ->assertRedirect(route('admin.persons.edit', $real));

        $this->assertNull(Person::withTrashed()->find($dup->id));
        $this->assertDatabaseHas('person_post', ['person_id' => $real->id, 'post_id' => $post->id]);
        $this->assertDatabaseHas('person_aliases', ['person_id' => $real->id, 'name' => 'Peter Beň']);
    }

    public function test_zmazanie_osoby_aj_s_vazbami(): void
    {
        $person = $this->person('Mikuláš Liptovský');
        $person->posts()->attach(Post::factory()->create()->id, ['source' => 'title', 'confidence' => 90]);

        $this->actingAs($this->admin())->delete(route('admin.persons.destroy', $person))->assertRedirect(route('admin.persons.index'));

        $this->assertNull(Person::withTrashed()->find($person->id));
        $this->assertDatabaseMissing('person_post', ['person_id' => $person->id]);
    }

    public function test_osoby_videa_z_detailu_prispevku(): void
    {
        $post = Post::factory()->create(['published_at' => now()]);
        $known = $this->person('Ján Buc');
        $this->actingAs($this->admin());

        $this->get(route('admin.persons.post', $post))->assertOk();
        session()->flush();
        $this->actingAs($this->admin())->get(route('post.show', [$post->id, $post->slug]))->assertSee(route('admin.persons.post', $post), false);

        $this->post(route('admin.persons.post.attach', $post), ['person' => "Ján Buc (#{$known->id})"]);
        $this->post(route('admin.persons.post.attach', $post), ['person' => 'Nový Rečník', 'create' => '1']);

        $this->assertSame(['Ján Buc', 'Nový Rečník'], $post->persons()->orderBy('name')->pluck('name')->all());
        $this->assertNotNull($post->fresh()->persons_checked_at);

        $this->delete(route('admin.persons.post.detach', [$post, $known]));
        $this->assertSame(['Nový Rečník'], $post->persons()->pluck('name')->all());
    }

    public function test_nova_osoba(): void
    {
        $this->actingAs($this->admin())->get(route('admin.persons.create'))->assertOk();
        $this->post(route('admin.persons.store'), ['first_name' => 'Jana', 'last_name' => 'Nová', 'aliases' => 'Janka Nová'])->assertRedirect();

        $person = Person::where('name', 'Jana Nová')->firstOrFail();
        $this->assertSame('jana-nova', $person->slug);
        $this->assertSame(['Janka Nová'], $person->aliases()->pluck('name')->all());
        $this->get(route('admin.persons.edit', $person))->assertOk()->assertSee('Zlúčiť do osoby');
    }
}
