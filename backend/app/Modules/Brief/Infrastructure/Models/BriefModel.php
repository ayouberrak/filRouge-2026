<?php

namespace App\Modules\Brief\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;

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
        'objectives',
        'date_start',
        'date_end',
        'difficulty',
        'modality',
        'pedagogical_modalities',
        'evaluation_modalities',
        'status',
        'points',
        'tags',
        'resources',
        'deliverables',
        'performance_criteria',
        'target_competencies',
        'file',
        'formateur_id'
    ];

    protected $casts = [
        'objectives' => 'array',
        'tags' => 'array',
        'resources' => 'array',
        'deliverables' => 'array',
        'performance_criteria' => 'array',
        'target_competencies' => 'array',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(ClassroomModel::class, 'brief_classroom', 'brief_id', 'classroom_id');
    }

    public function livrables()
    {
        return $this->hasMany(LivrableModel::class, 'brief_id');
    }

    public function quizSessions()
    {
        return $this->hasMany(\App\Modules\Quiz\Infrastructure\Models\QuizSessionModel::class, 'brief_id');
    }
}
