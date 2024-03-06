<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $numberOfOptions = $this->faker->numberBetween(3, 6);
        $correctAnswersCount = $this->faker->numberBetween(1, $numberOfOptions);
        $points = $this->faker->numberBetween(5, 20);

        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph,
            'points_question' => $points,
            'correct_answers_count' => $correctAnswersCount,
            'status' => 1,
        ];
    }
}
