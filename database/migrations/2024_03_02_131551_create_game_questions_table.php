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
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('question_id')->references('id')->on('questions');
            $table->unsignedInteger('game_id')->references('id')->on('games');
            $table->smallInteger("status_answered")->default(2)->comment('1:user answered, 2:user not answered, 3:user partly answered');
            $table->smallInteger("status_exposed")->default(2)->comment('1:user seen the question, 2:Not seen');
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
        Schema::dropIfExists('game_questions');
    }
};
