<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->string('first_name', 50);
            $table->string('last_name', 50)->nullable();
            $table->string('email', 100)->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password', 191);
            $table->boolean('verified')->default(false);
            $table->string('avatar', 200)->nullable();
            $table->string('vocative', 15)->nullable();
            $table->string('gender', 10)->nullable();
            $table->text('description')->nullable();
            $table->string('slug', 230);
            $table->boolean('send_email')->default(true);
            $table->boolean('front_author')->default(false);
            // Starý príznak blokácie; stav účtu drží `status` (App\Enums\UserStatus).
            $table->boolean('disabled')->default(false);
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('status_changed_at')->nullable();
            $table->unsignedInteger('status_changed_by')->nullable();
            $table->string('status_reason', 500)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_via', 32)->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('set_denomination')->nullable();
            // Aktívny kanál — smie to byť len kanál, ktorý užívateľ spravuje (canal_user).
            $table->integer('canal_id')->nullable();
            $table->string('api_token', 60);
            $table->dateTime('notify_bell')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
