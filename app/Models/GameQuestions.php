<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameQuestions extends Model
{
    use HasFactory;

    const STATUS_EXPOSED = 1;
    const STATUS_NOT_EXPOSED = 2;

    const STATUS_ANSWERED = 1;
    const STATUS_NOT_ANSWERED = 2;
    const STATUS_PARTLY_ANSWERED = 3;

    protected $fillable = ['question_id', 'game_id', 'status_answered', 'status_exposed'];

    public static function handleBulkInsertData($questions, $game_id) {
        $game_questions_bulk_data = [];

        foreach ($questions as $qa) {

            $game_questions_bulk_data[] = [
                'question_id' => $qa->id,
                'game_id' => $game_id,
                'status_answered' => 2,
                'status_exposed' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        return $game_questions_bulk_data;
    }

    public static function getNotCompletedQuestion($game_id, $question_id) {
        return self::where([
            'game_id' => $game_id,
            'question_id' => $question_id,
            'status_exposed' => self::STATUS_EXPOSED,
        ])
        ->where(function ($query) {
            $query->where('status_answered', self::STATUS_NOT_ANSWERED)
                ->orWhere('status_answered', self::STATUS_PARTLY_ANSWERED);
        });
    }

    public static function notExposedQuestionsCount($game_id, $question_id) {
        return self::select('question_id')
                ->where([
                    'game_id' => $game_id,
                    ['question_id', '<>', $question_id],
                    'status_exposed' => GameQuestions::STATUS_NOT_EXPOSED
                ])->count();
    }

}
