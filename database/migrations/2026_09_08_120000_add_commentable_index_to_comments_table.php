<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Komentáre visia na príspevku polymorfne, ale `comments` mala doteraz index
 * len na `id`, `user_id` a `(deleted_at, created_at)`. Každý dopyt, ktorý ide
 * z príspevkov do komentárov — počet komentárov kanála na jeho profile aj
 * súhrn na nástenke — preto skenoval celú tabuľku (~66 000 riadkov): samotný
 * súhrn komentárov kanála trval 0,52 s.
 *
 * Poradie stĺpcov kopíruje dopyt: najprv rovnosti (`commentable_type`,
 * `commentable_id`), potom `deleted_at`, ktoré k nim pridá SoftDeletes. S ním
 * je index krycí a `count(*)` sa číta priamo z neho.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comments')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->index(
                ['commentable_type', 'commentable_id', 'deleted_at'],
                'comments_commentable_index'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('comments')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_commentable_index');
        });
    }
};
