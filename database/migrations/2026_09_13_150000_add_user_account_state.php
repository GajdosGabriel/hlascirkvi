<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->index()->after('disabled');
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->unsignedInteger('status_changed_by')->nullable()->after('status_changed_at');
            $table->string('status_reason', 500)->nullable()->after('status_changed_by');
            $table->timestamp('last_login_at')->nullable()->after('status_reason');
            $table->string('last_login_via', 32)->nullable()->after('last_login_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_via');
        });

        // Zachovanie blokácií zo starého boolean stĺpca. Čas blokácie
        // nevymýšľame, preto ostane pri historických záznamoch neznámy.
        DB::table('users')->where('disabled', true)->update(['status' => 'blocked']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'status_changed_at',
                'status_changed_by',
                'status_reason',
                'last_login_at',
                'last_login_via',
                'last_login_ip',
            ]);
        });
    }
};
