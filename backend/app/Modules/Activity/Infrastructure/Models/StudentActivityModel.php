<?php

namespace App\Modules\Activity\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Model;

class StudentActivityModel extends Model
{
    protected $table = 'activity_student';

    protected $fillable = [
        'activity_id',
        'student_id',
        'status',
        'started_at',
        'completed_at',
    ];

    public function activity()
    {
        return $this->belongsTo(ActivityModel::class, 'activity_id');
    }

    public function student()
    {
        return $this->belongsTo(UserModel::class, 'student_id');
    }
}
