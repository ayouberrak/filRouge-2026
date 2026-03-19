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
        'description',
        'objectives',
        'date_start',
        'date_end',
        'difficulty',
        'modality',
        'status',
        'points',
        'tags',
        'resources',
        'file',
        'formateur_id'
    ];

    protected $casts = [
        'tags' => 'array',
        'resources' => 'array',
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
}
