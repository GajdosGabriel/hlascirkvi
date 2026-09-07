<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Panel "Naj z kanála" na detaile príspevku radí archív kanála podľa
 * count_view. Index posts_organization_created_index vie nájsť riadky kanála,
 * ale zoradiť ich musí filesortom — pri kanáloch s tisíckami príspevkov na
 * každom zobrazení článku. Tento index nesie poradie so sebou.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->index(
                ['organization_id', 'youtube_blocked', 'deleted_at', 'count_view'],
                'posts_organization_views_index'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_organization_views_index');
        });
    }
};
