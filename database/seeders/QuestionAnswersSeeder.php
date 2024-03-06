<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;


class QuestionAnswersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Generate 100 questions
        Question::factory(100)->create()->each(function ($question) {

            // Random number of answer options (between 3 and 6)
            $numberOfOptions = rand(3, 6);

            if ($question->correct_answers_count > $numberOfOptions - 1) {
                $correctAnswersCount = $numberOfOptions - 1;
            } else {
                $correctAnswersCount = $question->correct_answers_count;
            }


            $points = $question->points_question;

            // Distribute points for each correct answer
            $pointsPerCorrectAnswer = floor($points / $correctAnswersCount);
            $remainingPoints = ceil($points % $correctAnswersCount);

            $answers = QuestionAnswer::factory($numberOfOptions)->make();
            $answers->shuffle();

            $correctAnswersAssigned = 0;

            // Assign correct or wrong status to each answer
            $answers->each(function ($answer, $index) use ($question, $correctAnswersCount, $pointsPerCorrectAnswer, $remainingPoints, &$correctAnswersAssigned) {

                $isCorrect = ($correctAnswersAssigned < $correctAnswersCount) && (rand(0, 1) || $correctAnswersAssigned == 0);

                if ($isCorrect) {

                    // If the answer is correct
                    if ($correctAnswersAssigned < $correctAnswersCount - 1) {
                        $answer->status_answer = 1;
                        $answer->points_answer = $pointsPerCorrectAnswer;
                    } elseif ($correctAnswersAssigned == $correctAnswersCount - 1) {
                        $answer->status_answer = 1;
                        $answer->points_answer = $pointsPerCorrectAnswer + $remainingPoints; // Add remaining points
                    }

                    $correctAnswersAssigned++;

                } else {
                    // If the answer is wrong
                    $answer->status_answer = 2; // Wrong answer
                    $answer->points_answer = 0;
                }

                $question->answers()->save($answer);
            });
        });
    }
}
