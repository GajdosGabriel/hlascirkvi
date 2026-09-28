<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VerifiedCanalProfilesMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'profile_migration_test', 'database.connections.profile_migration_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('canals', function (Blueprint $table) {
            $table->integer('id')->primary();
            foreach (['title', 'slug', 'identity_mode', 'url_www', 'email', 'phone', 'phone_numeric', 'street', 'description'] as $field) {
                $table->text($field)->nullable();
            }
            $table->integer('village_id')->default(242);
            $table->integer('psc')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
        (require database_path('migrations/2026_09_28_100000_add_canal_coordinates.php'))->up();
    }

    protected function tearDown(): void
    {
        DB::purge('profile_migration_test');
        parent::tearDown();
    }

    private function profiles(): array
    {
        return json_decode(file_get_contents(database_path('data/canal-profiles-2026-09-28.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function seedProfiles(): void
    {
        foreach ($this->profiles() as $profile) {
            DB::table('canals')->insert([
                'id' => $profile['id'], 'title' => $profile['title'], 'slug' => $profile['slug'],
                'identity_mode' => 'organization',
                'village_id' => $profile['coordinates']['village_id'] ?? 242,
                'street' => $profile['coordinates']['street'] ?? null,
            ]);
        }
    }

    private function migrateProfiles(): void
    {
        (require database_path('migrations/2026_09_28_110000_fill_verified_canal_profiles.php'))->up();
    }

    public function test_all_reviewed_profiles_have_sources_and_ten_sentences_in_three_paragraphs(): void
    {
        $ids = [];
        foreach ($this->profiles() as $profile) {
            $this->assertNotContains($profile['id'], $ids);
            $ids[] = $profile['id'];
            $this->assertNotEmpty($profile['sources']);
            foreach ($profile['sources'] as $source) {
                $this->assertNotFalse(filter_var($source, FILTER_VALIDATE_URL));
            }
            if (isset($profile['paragraphs'])) {
                $this->assertCount(3, $profile['paragraphs']);
                $this->assertSame(10, preg_match_all('/[.!?](?:\s|$)/u', implode(' ', $profile['paragraphs'])), $profile['title']);
                foreach ($profile['paragraphs'] as $paragraph) {
                    $this->assertSame(strip_tags($paragraph), $paragraph);
                }
            }
            if (isset($profile['coordinates'])) {
                $this->assertGreaterThanOrEqual(-90, $profile['coordinates']['latitude']);
                $this->assertLessThanOrEqual(90, $profile['coordinates']['latitude']);
                $this->assertGreaterThanOrEqual(-180, $profile['coordinates']['longitude']);
                $this->assertLessThanOrEqual(180, $profile['coordinates']['longitude']);
                $this->assertNotEmpty($profile['coordinates']['source_url']);
            }
            foreach ($profile['fields'] as $field => $value) {
                $limit = ['email' => 100, 'phone' => 20, 'url_www' => 191, 'street' => 191][$field] ?? 20;
                $this->assertLessThanOrEqual($limit, mb_strlen((string) $value));
            }
        }
    }

    public function test_fills_profiles_and_coordinate_pairs_and_is_idempotent(): void
    {
        $this->seedProfiles();
        $this->migrateProfiles();
        foreach ($this->profiles() as $profile) {
            $row = DB::table('canals')->find($profile['id']);
            if (isset($profile['paragraphs'])) {
                $this->assertSame(3, substr_count($row->description, '<p>'));
            }
            if (isset($profile['coordinates'])) {
                $this->assertEquals($profile['coordinates']['latitude'], $row->latitude);
                $this->assertEquals($profile['coordinates']['longitude'], $row->longitude);
            }
            if (isset($profile['fields']['phone'])) {
                $this->assertSame(preg_replace('/\D/', '', $profile['fields']['phone']), $row->phone_numeric);
            }
        }
        $before = DB::table('canals')->orderBy('id')->get()->toJson();
        $this->travel(1)->hours();
        $this->migrateProfiles();
        $this->assertSame($before, DB::table('canals')->orderBy('id')->get()->toJson());
    }

    public function test_preserves_editorial_values_and_rejects_different_identity_and_address(): void
    {
        $this->seedProfiles();
        DB::table('canals')->where('id', 594)->update(['email' => 'editor@example.sk', 'phone' => '0900123456', 'phone_numeric' => '0900123456', 'description' => '<p>Vlastný opis.</p>', 'street' => 'Iná 1']);
        DB::table('canals')->where('id', 595)->update(['latitude' => 49.1]);
        DB::table('canals')->where('id', 648)->update(['village_id' => 999]);
        DB::table('canals')->where('id', 650)->update(['title' => 'Iná organizácia']);
        DB::table('canals')->where('id', 658)->update(['identity_mode' => 'person']);
        DB::table('canals')->where('id', 745)->update(['deleted_at' => now()]);
        DB::table('canals')->where('id', 755)->update(['slug' => 'iny-kanal']);
        $before = DB::table('canals')->whereIn('id', [650, 658, 745, 755])->get()->toJson();
        $this->migrateProfiles();
        $row = DB::table('canals')->find(594);
        $this->assertSame('editor@example.sk', $row->email);
        $this->assertSame('0900123456', $row->phone);
        $this->assertSame('0900123456', $row->phone_numeric);
        $this->assertSame('<p>Vlastný opis.</p>', $row->description);
        $this->assertNull($row->latitude);
        $this->assertNull(DB::table('canals')->find(595)->longitude);
        $this->assertNull(DB::table('canals')->find(648)->latitude);
        $this->assertSame($before, DB::table('canals')->whereIn('id', [650, 658, 745, 755])->get()->toJson());
    }
}
