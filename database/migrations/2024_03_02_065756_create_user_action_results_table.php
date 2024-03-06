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
        Schema::create('user_action_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_action_id')->references('id')->on('user_actions');
            $table->unsignedInteger('answer_id')->references('id')->on('user_actions');
            $table->smallInteger('status_answer')->default(2)->comment('1:correct, 2:wrong');
            $table->unsignedInteger('points_answer')->default(0)->comment('Points earned from correct answer');
            $table->index('user_action_id');
            $table->index('answer_id');
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
        Schema::dropIfExists('user_action_results');
    }
};
