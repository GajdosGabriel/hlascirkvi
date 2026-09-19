<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('districts')) {
            return;
        }

        Schema::create('districts', function (Blueprint $table) {
            // Číselník okresov SR, prevzatý aj s MyISAM a utf8.
            $table->engine('MyISAM');
            $table->charset('utf8');
            $table->collation('utf8_unicode_ci');

            $table->integer('id', true)->comment('Unique identifier');
            $table->string('name')->comment('Name');
            $table->string('veh_reg_num')->comment('Vehicle registration number of district');
            $table->smallInteger('code')->comment('Code');
            $table->integer('region_id')->comment('ID of region where district belongs to');
            $table->tinyInteger('use')->default(1)->comment('1 = use the row, 0 = not');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
