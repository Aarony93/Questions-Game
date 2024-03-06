<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameQuestions;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\UserActionResults;
use App\Models\UserActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{


    public function start(Request $request) {

        if (!$user_id = auth()->id()) abort(401);

        $game = Game::where(['user_id' => $user_id, 'status_game' => Game::STATUS_NOT_FINISHED])->first();

        if (!$game) {

            $used_question_ids = [];

            $user_used_question_ids = UserActions::where(['user_id' => $user_id])->get('question_id');
            if ($user_used_question_ids) {
                $user_used_question_ids = array_column($user_used_question_ids->toArray(), 'question_id');
            }

            $used_game_questions = GameQuestions::get('question_id');
            if ($used_game_questions) {
                $used_game_questions = array_column($used_game_questions->toArray(), 'question_id');
            }

            $used_question_ids = array_merge($user_used_question_ids, $used_game_questions);
            $questions = Question::getGameQuestions($used_question_ids ? $used_question_ids : []);

            $qTotals = Question::calculateQuestionsTotals($questions);
            $game_last_id = Game::latest()->value('id') + 1;

            $game_data = Game::handleInsertData($user_id, $game_last_id, $qTotals['totalPoints'], $qTotals['totalCorrectAnswers']);

            $game = Game::create($game_data);

            $game_questions_bulk_data = GameQuestions::handleBulkInsertData($questions, $game->id);
            GameQuestions::insert($game_questions_bulk_data);
        }

        // if there was a multiple answer question that is partly answered, bring it up first!
        $exposed_question = GameQuestions::where([
            'game_id' => $game->id,
            'status_exposed' => GameQuestions::STATUS_EXPOSED,
            'status_answered' => GameQuestions::STATUS_PARTLY_ANSWERED]
        )->first();

        $correct_answer_ids = [];


        // if there was a half answered multiple question, then we must show the correct answers of user if available
        if ($exposed_question) {

            $user_action = UserActions::select('id')->where([
                'user_id' => $user_id,
                'game_id' => $game->id,
                'question_id' => $exposed_question->question_id,
            ])->first();

            if ($user_action) {
                $user_action_results = UserActionResults::where([
                    'user_action_id' => $user_action->id
                ])->get();

                if ($user_action_results) {
                    foreach($user_action_results as $user_action_result) {

                        if ($user_action_result->status_answer == UserActionResults::STATUS_ANSWER_CORRECT) {

                            $correct_answer_ids[] = $user_action_result->answer_id;
                        }
                    }
                }
            }

        }

        if (!$exposed_question) {

            $exposed_question = GameQuestions::where([
                'game_id' => $game->id,
                'status_exposed' => GameQuestions::STATUS_EXPOSED,
                'status_answered' => GameQuestions::STATUS_NOT_ANSWERED]
            )->first();
        }

        // $game_points = 0;
        $current_question = '';
        if ($exposed_question) {

            // $game_points = UserActions::getUserSingleGameResults($user_id, $game->id);

            // get the exposed but not answered question with answers
            $current_question = Question::select('questions.id as question_id', 'questions.title', 'questions.body', 'points_question', 'correct_answers_count',
                DB::raw("CONCAT('[', GROUP_CONCAT('{\"id\":', question_answers.id, ',\"body\":', '\"' , question_answers.body, '\"', ',\"points_answer\":', '\"' , question_answers.points_answer, '\"' , '}'), ']') as answers"))
                ->leftJoin('question_answers', 'question_answers.question_id', '=', 'questions.id')
                ->where([
                    'questions.status' => Question::STATUS_ACTIVE,
                    'question_answers.question_id' => $exposed_question->question_id
                ])
                ->first();

            if ($current_question) {
                $current_question->answers = json_decode($current_question->answers, 1);
            }

        } else {

            // get a random one from Game Questions
            $current_question = GameQuestions::select('game_questions.id','questions.id as question_id', 'questions.title', 'questions.body', 'points_question', 'correct_answers_count',
                    DB::raw("CONCAT('[', GROUP_CONCAT('{\"id\":', question_answers.id, ',\"body\":', '\"' , question_answers.body, '\"', ',\"points_answer\":', '\"' , question_answers.points_answer, '\"' , '}'), ']') as answers"),
                )
                ->leftJoin('questions', 'questions.id', '=', 'game_questions.question_id')
                ->leftJoin('question_answers', 'question_answers.question_id', '=', 'questions.id')
                ->where([
                    'game_id' => $game->id,
                    'status_exposed' => GameQuestions::STATUS_NOT_EXPOSED
                ])
                ->groupBy('questions.id')
                ->inRandomOrder()
                ->first();

            if ($current_question) {
                $current_question->answers = json_decode($current_question->answers, 1);

                if ($gq = GameQuestions::where('id' , $current_question->id)->first()) {
                    $gq->status_exposed = GameQuestions::STATUS_EXPOSED;
                    $gq->save();
                }
            }

        }


        $exposed_questions_count = GameQuestions::where([
            'game_id' => $game->id,
            'status_exposed' => GameQuestions::STATUS_EXPOSED,
            'status_answered' => GameQuestions::STATUS_ANSWERED]
        )->count();
        $question_number = ($exposed_questions_count + 1) > 5 ? 5 : ($exposed_questions_count + 1);

        $game_finished_status = 0;
        if ($question_number > 4) {
            $game_finished_status = 1;
        }

        // if (!$current_question) abort(404);

        return view('game', [
            'game_id' => $game->id,
            'question' => $current_question,
            'question_number' => $question_number,
            'correct_answered_ids' => $correct_answer_ids,
            // 'game_points' => $game_points,
            'game_finished_status' => $game_finished_status,
        ]);
    }


    public function checkAnswer(Request $request) {

        if (!$user_id = auth()->id()) abort(401);
        if (!$request->ajax()) abort(404);

        // TODO: validate client sent data

        $game_id = intval($request->game_id);
        $question_id = intval($request->question_id);
        $answer_id = intval($request->answer_id);

        if (!$game_instance = Game::getUsersCurrentGame($game_id, $user_id)) abort(404);

        // not answered or partly answered question
        $exposed_question_instance = GameQuestions::getNotCompletedQuestion($game_id, $question_id);

        if (!$exposed_question = $exposed_question_instance->first()) abort(404);

        // get the exposed question with answers
        $current_question = Question::getQuestionWithAnswers($question_id);

        if ($current_question) {

            $current_question->answers = json_decode($current_question->answers, 1);
            $current_question = $current_question->toArray();

            $correct_answers_count = $current_question['correct_answers_count'];
            $multiple_correct_answers = $correct_answers_count > 1 ? true : false;

            if ($multiple_correct_answers) {
                $exposed_question->status_answered = GameQuestions::STATUS_PARTLY_ANSWERED;
            } else {
                $exposed_question->status_answered = GameQuestions::STATUS_ANSWERED;
            }
            $exposed_question->save();

            $correct_answer_ids = [];
            $correct_answer_id_points = [];
            $current_answer_is_correct = false;
            $current_answer_earned_points = 0;
            foreach($current_question['answers'] as $answer) {

                if ($answer['status_answer'] == QuestionAnswer::STATUS_ANSWER_CORRECT) {

                    $correct_answer_ids[] = $answer['id'];
                    $correct_answer_id_points[$answer['id']] = $answer['points_answer'];
                }
            }

            if (in_array($answer_id, $correct_answer_ids)) {
                $current_answer_is_correct = true;
                $current_answer_earned_points = $correct_answer_id_points[$answer_id];
            }


            $user_action = UserActions::_save($user_id, $game_id, $question_id);
            UserActionResults::_save($user_action->id, $answer_id, $current_answer_is_correct ? 1 : 2, $current_answer_is_correct ? $current_answer_earned_points : 0);

            $user_actions_count = UserActionResults::where([
                'user_action_id' => $user_action->id,
            ])->count();


            $game_questions_left = GameQuestions::notExposedQuestionsCount($game_id, $question_id);

            $go_to_next_question_status = 0;

            $multiple_answer_message = "The game is finished!";
            if ($game_questions_left) {

                $go_to_next_question_status = 1;
                $multiple_answer_message = "Let's go to the next question!";
            }



            if ($multiple_correct_answers) {

                if ($current_answer_is_correct) {

                    if ($user_actions_count == $correct_answers_count) {

                        if ($game_questions_left) {

                            $go_to_next_question_status = 1;
                            $multiple_answer_message = "Let's go to the next question!";
                        } else {

                            $go_to_next_question_status = 0;
                            $multiple_answer_message = "The game is finished!";
                        }

                        $exposed_question->status_answered = GameQuestions::STATUS_ANSWERED;
                        $exposed_question->save();

                    } else {

                        $go_to_next_question_status = 0;
                        $multiple_answer_message = "There are more correct answers, try to find them!";
                    }

                } else {

                    $exposed_question->status_answered = GameQuestions::STATUS_ANSWERED;
                    $exposed_question->save();

                    $go_to_next_question_status = 1;
                    $multiple_answer_message = "Let's go to the next question!";
                }
            }

            $game_points = UserActions::getUserSingleGameResults($user_id, $game_id);

            // determine if the game is finished
            $user_actions_on_current_game_count = UserActions::where([
                'user_id' => $user_id,
                'game_id' => $game_id,
            ])->count();

            $not_asnwered_game_questions_count = GameQuestions::where([
                'game_id' => $game_id,
                ['status_answered', '<>', GameQuestions::STATUS_ANSWERED],
            ])->count();

            $game_is_finished_status = 0;
            if ( $user_actions_on_current_game_count > 4 && empty($not_asnwered_game_questions_count)) {

                // end the game
                $game_instance->status_game = Game::STATUS_FINISHED;
                $game_instance->points_user = $game_points;
                $game_instance->save();


                $game_is_finished_status = 1;
                $multiple_answer_message = "The game is finished!";
            }



            if ($multiple_correct_answers) {
                // multiple correct answers

                if ($current_answer_is_correct) {

                    return response()->json(['message' => 'The answer is correct, you got ' . $current_answer_earned_points . ' points!',
                        'answer_status' => 1, 'multiple_answer_message' => $multiple_answer_message, 'multiple_answers_exist' => 1,
                        'points' => $current_answer_earned_points, 'correct_answer_ids' => [],
                        'correct_answer_message' => '',
                        'go_to_next_question_status' => $go_to_next_question_status,
                        'game_is_finished_status' => $game_is_finished_status,
                        'game_points' => $game_points,
                    ]);

                } else {

                    return response()->json(['message' => 'Unfortunately the answer is wrong!',
                        'answer_status' => 0, 'multiple_answer_message' => $multiple_answer_message, 'multiple_answers_exist' => 1,
                        'points' => 0, 'correct_answer_ids' => $correct_answer_ids,
                        'correct_answer_message' => 'The correct answers are colored green!',
                        'go_to_next_question_status' => $go_to_next_question_status,
                        'game_is_finished_status' => $game_is_finished_status,
                        'game_points' => $game_points,
                    ]);
                }

            } else {
                // single correct answer

                if ($current_answer_is_correct) {

                    return response()->json(['message' => 'The answer is correct, you got ' . $current_answer_earned_points . ' points!',
                        'answer_status' => 1, 'multiple_answer_message' => $multiple_answer_message, 'multiple_answers_exist' => 0,
                        'points' => $current_answer_earned_points, 'correct_answer_ids' => 0,
                        'correct_answer_message' => '',
                        'go_to_next_question_status' => $go_to_next_question_status,
                        'game_is_finished_status' => $game_is_finished_status,
                        'game_points' => $game_points,
                    ]);

                } else {

                    return response()->json(['message' => 'Unfortunately the answer is wrong!',
                        'answer_status' => 0, 'multiple_answer_message' => $multiple_answer_message, 'multiple_answers_exist' => 0,
                        'points' => 0, 'correct_answer_ids' => $correct_answer_ids,
                        'correct_answer_message' => 'The correct answers are colored green!',
                        'go_to_next_question_status' => $go_to_next_question_status,
                        'game_is_finished_status' => $game_is_finished_status,
                        'game_points' => $game_points,
                    ]);
                }
            }
        }


    }


    public static function getGameScore(Request $request)  {
        if (!$user_id = auth()->id()) abort(401);
        if (!$request->ajax()) abort(404);

        // TODO: validate client sent data

        $game_id = intval($request->game_id);

        if (!Game::getUsersCurrentGame($game_id, $user_id)) {
            return response()->json(['score' => 0]);
        }

        $game_points = UserActions::getUserSingleGameResults($user_id, $game_id);
        return response()->json(['score' => $game_points]);
    }


}
