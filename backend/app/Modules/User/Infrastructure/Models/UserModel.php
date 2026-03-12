<?php

namespace App\Modules\User\Infrastructure\Models;

use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Deliverable\Infrastructure\Models\DeliverableModel;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\Report\Infrastructure\Models\DailyReportModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'status',
        'speciality',
        'points',
        'classroom_id',
        'squad_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function classroom()
    {
        return $this->belongsTo(ClassroomModel::class);
    }

    public function managedClassrooms()
    {
        return $this->hasMany(ClassroomModel::class, 'formateur_id');
    }

    public function squad()
    {
        return $this->belongsTo(SquadModel::class);
    }

    public function brief()
    {
        return $this->hasMany(BriefModel::class, 'formateur_id');
    }

    public function deliverables()
    {
        return $this->hasMany(DeliverableModel::class, 'student_id');
    }

    public function absences()
    {
        return $this->hasMany(AbsenceModel::class, 'student_id');
    }

    public function activities()
    {
        return $this->belongsToMany(ActivityModel::class, 'activity_user');
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReportModel::class);
    }

}
