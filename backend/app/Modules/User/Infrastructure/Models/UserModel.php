<?php

namespace App\Modules\User\Infrastructure\Models;

use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;


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
        'classroom_id',
        'squad_id',
        'github_url',
        'linkedin_url',
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
        return $this->belongsTo(ClassroomModel::class, 'classroom_id');
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

    public function livrables()
    {
        return $this->hasMany(LivrableModel::class, 'student_id');
    }

    public function absences()
    {
        return $this->hasMany(AbsenceModel::class, 'student_id');
    }

    public function activities()
    {
        return $this->belongsToMany(ActivityModel::class, 'activity_student', 'student_id', 'activity_id');
    }


}
