<?php

namespace App\Modules\Quiz\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionModel extends Model
{
    use HasFactory;

    protected $table = 'questions';

    protected $fillable = [
        'quiz_session_id',
        'type',
        'content',
        'correct_answer',
        'context_data',
        'points'
    ];

    protected $casts = [
        'context_data' => 'array',
        'points' => 'integer',
    ];

    public function quizSession(): BelongsTo
    {
        return $this->belongsTo(QuizSessionModel::class, 'quiz_session_id');
    }

    public function studentResponses(): HasMany
    {
        return $this->hasMany(StudentResponseModel::class, 'question_id');
    }
}
