<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prayers', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->integer('user_id');
            $table->string('user_name')->nullable();
            $table->text('body');
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('last_notification')->nullable();
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prayers');
    }
}
