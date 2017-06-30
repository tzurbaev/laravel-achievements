<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAchievementCriteriablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('achievement_criteriables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('achievement_criteriable_type')->index();
            $table->unsignedInteger('achievement_criteriable_id')->index();
            $table->unsignedInteger('achievement_criteria_model_id')->index();
            $table->unsignedInteger('value')->index();
            $table->boolean('completed')->default(false)->index();
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('achievement_criteriables');
    }
}
