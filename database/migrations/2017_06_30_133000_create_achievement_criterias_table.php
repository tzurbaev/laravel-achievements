<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchievementCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achievement_criterias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('achievement_id')->index();
            $table->string('type')->index();
            $table->string('name')->nullable();
            $table->unsignedInteger('max_value')->index();
            $table->json('requirements')->nullable();
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
        Schema::dropIfExists('achievement_criterias');
    }
}
