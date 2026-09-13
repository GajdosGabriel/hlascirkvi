<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TagsRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_tags_module_is_removed(): void
    {
        $this->assertFalse(Route::has('admin.tag.index'));
        $this->assertFalse(Route::has('admin.tag.store'));
        $this->assertFalse(Route::has('admin.tag.destroy'));
        $this->assertFalse(Schema::hasTable('tags'));
        $this->assertFalse(Schema::hasTable('post_tag'));
    }
}
