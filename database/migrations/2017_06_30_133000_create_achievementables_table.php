<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchievementablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achievementables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('achievementable_type')->index();
            $table->unsignedInteger('achievementable_id')->index();
            $table->unsignedInteger('achievement_model_id')->index();
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('achievementables');
    }
}
