<?php

namespace App\Modules\Quiz\Infrastructure\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Modules\Brief\Infrastructure\Models\BriefModel;

class QuizSessionModel extends Model
{
    use HasFactory;

    protected $table = 'quiz_sessions';

    protected $fillable = [
        'brief_id',
        'formateur_id',
        'status',
        'timer_minutes',
        'passing_score',
    ];

    protected $casts = [
        'timer_minutes' => 'integer',
        'passing_score' => 'integer',
    ];

    public function brief(): BelongsTo
    {
        return $this->belongsTo(BriefModel::class, 'brief_id');
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionModel::class, 'quiz_session_id');
    }
}
