<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_users', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('email')->unique();
            $table->json('snapshot');
            $table->string('kind', 20)->default('legacy');
            $table->timestamps();
        });

        foreach (\App\Services\PendingUsers::RELATIONS as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedInteger('user_id')->nullable()->change();
                $table->unsignedInteger('pending_user_id')->nullable()->index();
            });
        }
        Schema::table('comments', fn (Blueprint $table) => $table->string('source', 20)->default('site')->index());
        DB::table('comments')->whereNotNull('youtube_comment_id')
            ->orWhere(fn ($q) => $q->where('user_id', 100)->where('user_avatar', 'like', 'https://yt3.%'))
            ->update(['source' => 'youtube', 'user_id' => null]);

        // Known historical placeholder, never a registered visitor.
        $technical = DB::table('users')->where('id', 100)->where('first_name', 'Neznámy')->where('last_name', 'autor')->first();
        if ($technical) {
            DB::table('comments')->where('user_id', 100)->update(['source' => 'system', 'user_id' => null]);
            app(\App\Services\PendingUsers::class)->move(100, technical: true);
        }

        DB::table('users')->whereNull('email_verified_at')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                app(\App\Services\PendingUsers::class)->move($user->id);
            }
        });
    }

    public function down(): void
    {
        // Data-preserving migration: pending accounts must not become users
        // again without proving their email address.
        throw new RuntimeException('Presun čakajúcich účtov vyžaduje obnovu zo zálohy.');
    }
};
