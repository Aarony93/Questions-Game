<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->references('id')->on('users');
            $table->string('title');
            $table->unsignedInteger('points_total')->default(0)->comment('Total Points of the game');
            $table->unsignedInteger('points_user')->default(0)->comment('Points earned by a user');
            $table->unsignedInteger('correct_answers_count')->default(0);
            $table->smallInteger('status_game')->default(1)->comment('1:Not finished, 2:Finished');
            $table->index('user_id');
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
        Schema::dropIfExists('games');
    }
};
