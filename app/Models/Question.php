<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Question extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_DELETED = 3;

    // protected $fillable = ['title', 'body', 'points', 'correct_answers_count', 'status'];

    public static function getGameQuestions($excluded_question_ids = []) {

        $placeholders = $exclude_where_condition = '';
        $bindings = [Question::STATUS_ACTIVE];

        if ($excluded_question_ids) {
            $placeholders = rtrim(str_repeat('?,', count($excluded_question_ids)), ',');
            $exclude_where_condition = " AND questions.id NOT IN ($placeholders) ";

            $bindings = array_merge([Question::STATUS_ACTIVE], $excluded_question_ids);
        }

        // return DB::select("
        //     SELECT
        //         questions.*,
        //         question_answers.id answer_id,
        //         question_answers.body answer_body,
        //         question_answers.status_answer,
        //         question_answers.points_answer
        //     FROM
        //         questions
        //     LEFT JOIN question_answers ON question_answers.question_id = questions.id
        //     WHERE
        //         questions.status = ?
        //         $exclude_where_condition
        //     ORDER BY
        //         RAND()
        //     LIMIT
        //         5
        // ", $bindings);

        return DB::select("
            SELECT
                questions.*,
                MAX(question_answers.id) AS answer_id,
                MAX(question_answers.body) AS answer_body,
                MAX(question_answers.status_answer) AS status_answer,
                MAX(question_answers.points_answer) AS points_answer
            FROM
                questions
            LEFT JOIN question_answers ON question_answers.question_id = questions.id
            WHERE
                questions.status = ?
                $exclude_where_condition
            GROUP BY
                questions.id
            ORDER BY
                RAND()
            LIMIT
                5
        ", $bindings);

    }

    public static function calculateQuestionsTotals($questions) {
        $totalPoints = 0;
        $totalCorrectAnswers = 0;

        foreach ($questions as $qa) {

            $totalPoints += $qa->points_question;

            if ($qa->status_answer == 1) {
                ++$totalCorrectAnswers;
            }
        }

        return [
            'totalPoints' => $totalPoints,
            'totalCorrectAnswers' => $totalCorrectAnswers
        ];
    }


    public static function getQuestionWithAnswers($question_id) {
        return self::select('questions.id as question_id', 'questions.title', 'questions.body', 'points_question', 'correct_answers_count',
            DB::raw("CONCAT('[', GROUP_CONCAT('{\"id\":', question_answers.id, ',\"status_answer\":', '\"' , question_answers.status_answer, '\"', ',\"points_answer\":', '\"' , question_answers.points_answer, '\"' , '}'), ']') as answers"))
            ->leftJoin('question_answers', 'question_answers.question_id', '=', 'questions.id')
            ->where([
                'questions.status' => Question::STATUS_ACTIVE,
                'question_answers.question_id' => $question_id
            ])
            ->first();
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class);
    }

}
