<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'title', 'body', 'points_total', 'points_user', 'correct_answers_count', 'status_game'];

    // protected $table = 'games';
    // public $timestamps = false;

    const STATUS_NOT_FINISHED = 1;
    const STATUS_FINISHED = 2;

    public static function handleInsertData($user_id, $game_id, $totalPoints, $totalCorrectAnswers) {
        return [
            'user_id' => $user_id,
            'title' => 'Game #' . $game_id,
            'points_total' => $totalPoints,
            'points_user' => 0,
            'correct_answers_count' => $totalCorrectAnswers,
            'status_game' => 1, // not finished
        ];
    }

    public static function update_status($game_id, $user_id, $status) {
        if ($item = self::getGameByUser($game_id, $user_id)) {
            $item->status_game = $status;

            if ($item->save()) {
                return true;
            }
            return false;
        }
    }

    public static function getGameByUser($id, $user_id) {
        return self::where([
            'id' => $id,
            'user_id' => $user_id
        ])->first();
    }

    public static function getUsersCurrentGame($game_id, $user_id) {
        return self::where([
            'id' => $game_id,
            'user_id' => $user_id,
            'status_game' => self::STATUS_NOT_FINISHED,
        ])->first();
    }


    public static function getUserGames($user_id) {
        return self::where([
            'user_id' => $user_id,
            'status_game' => self::STATUS_FINISHED,
        ])->get();
    }


}
