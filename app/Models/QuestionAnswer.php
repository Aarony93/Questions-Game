<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    use HasFactory;

    // protected $fillable = ['question_id', 'body', 'status_answer', 'points'];

    const STATUS_ANSWER_CORRECT = 1;
    const STATUS_ANSWER_WRONG = 2;

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
