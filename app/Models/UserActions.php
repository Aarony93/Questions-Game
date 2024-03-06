<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserActions extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'game_id',
        'question_id',
    ];

    public static function _save($user_id, $game_id, $question_id , $id = 0) {
        $user_action = new self;

        $user_action_exists = self::where([
            'user_id' => $user_id,
            'game_id' => $game_id,
            'question_id' => $question_id,
        ])->first();

        if ($user_action_exists) {
            return $user_action_exists;
        }

        $user_action->user_id = $user_id;
        $user_action->game_id = $game_id;
        $user_action->question_id = $question_id;
        $user_action->save();

        return $user_action;
    }


    public static function getUserSingleGameResults($user_id, $game_id) {
        $user_points = 0;

        $user_actions = UserActions::select('user_actions.id',
                DB::raw("CONCAT('[', GROUP_CONCAT('{\"answer_id\":', user_action_results.answer_id, ',\"points_answer\":', '\"' , user_action_results.points_answer, '\"', ',\"status_answer\":', '\"' , user_action_results.status_answer, '\"' , '}'), ']') as results"),
            )
            ->leftJoin('user_action_results', 'user_action_results.user_action_id', '=', 'user_actions.id')
            ->where([
                'user_actions.user_id' => $user_id,
                'user_actions.game_id' => $game_id,
            ])
            ->groupBy('user_actions.id')
            ->get();

        if ($user_actions) {

            foreach($user_actions as $usr_act) {

                if ($usr_act->results) {

                    $usr_act->results = json_decode($usr_act->results);

                    foreach($usr_act->results as $res) {
                        if ($res->status_answer == UserActionResults::STATUS_ANSWER_CORRECT) {

                            $user_points += $res->points_answer;
                        }
                    }
                }
            }
        }

        return $user_points;
    }

}
