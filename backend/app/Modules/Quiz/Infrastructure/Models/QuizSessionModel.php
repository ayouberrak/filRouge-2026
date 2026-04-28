<?php

namespace App\Modules\Quiz\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Infrastructure\Models\UserModel;

class QuizSessionModel extends Model
{
    use HasFactory;

    protected $table = 'quiz_sessions';

    protected $fillable = [
        'formateur_id',
        'title',
        'description',
        'classroom_id',
        'status',
        'timer_minutes',
        'passing_score',
    ];

    protected $casts = [
        'timer_minutes' => 'integer',
        'passing_score' => 'integer',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Classroom\Infrastructure\Models\ClassroomModel::class, 'classroom_id');
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionModel::class, 'quiz_session_id');
    }
}
