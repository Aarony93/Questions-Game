<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActionResults extends Model
{
    use HasFactory;

    const STATUS_ANSWER_CORRECT = 1;
    const STATUS_ANSWER_WRONG = 2;

    protected $fillable = [
        'user_action_id',
        'answer_id',
        'status_answer',
        'points_answer',
    ];


    public static function _save($user_action_id, $answer_id, $status_answer, $points_answer, $id = 0) {
        $user_action_result = new self;

        $user_action_result->user_action_id = $user_action_id;
        $user_action_result->answer_id = $answer_id;
        $user_action_result->status_answer = $status_answer;
        $user_action_result->points_answer = $points_answer;
        $user_action_result->save();

        return $user_action_result;
    }
}
