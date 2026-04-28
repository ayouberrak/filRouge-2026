<?php

namespace App\Modules\Quiz\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Infrastructure\Models\UserModel;

class StudentResponseModel extends Model
{
    use HasFactory;

    protected $table = 'student_responses';

    protected $fillable = [
        'question_id',
        'student_id',
        'response_text',
        'score',
        'is_correct',
        'ai_feedback'
    ];

    protected $casts = [
        'score' => 'float',
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionModel::class, 'question_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'student_id');
    }
}
