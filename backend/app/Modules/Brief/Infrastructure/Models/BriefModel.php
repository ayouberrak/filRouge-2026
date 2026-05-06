<?php

namespace App\Modules\Brief\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Quiz\Infrastructure\Models\QuizSessionModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriefModel extends Model
{
    use HasFactory;
    protected $table = 'briefs';

    protected $fillable = [
        'title',
        'image_url',
        'description',
        'context',
        'date_start',
        'date_end',
        'modality',
        'status',
        'tags',
        'file',
        'formateur_id',
        'classroom_id'
    ];

    protected $casts = [
        'tags' => 'array',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function classroom()
    {
        return $this->belongsTo(ClassroomModel::class, 'classroom_id');
    }

    public function squads()
    {
        return $this->belongsToMany(SquadModel::class, 'brief_squad', 'brief_id', 'squad_id');
    }

    public function livrables()
    {
        return $this->hasMany(LivrableModel::class, 'brief_id');
    }

    public function quizSessions()
    {
        return $this->hasMany(QuizSessionModel::class, 'brief_id');
    }
}
