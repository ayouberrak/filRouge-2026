<?php

namespace App\Modules\Activity\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use Illuminate\Database\Eloquent\Model;

class ActivityModel extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'title',
        'description',
        'activity_type',
        'duration',
        'points',
        'formateur_id',
        'classroom_id',
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function classroom()
    {
        return $this->belongsTo(ClassroomModel::class, 'classroom_id');
    }

    public function students()
    {
        return $this->belongsToMany(UserModel::class, 'activity_student', 'activity_id', 'student_id')
            ->withPivot('status', 'started_at', 'completed_at')
            ->withTimestamps();
    }
}
