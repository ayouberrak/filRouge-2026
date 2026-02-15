<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        return $this->belongsTo(Classroom::class);
    }

    public function managedClassrooms()
    {
        return $this->hasMany(Classroom::class, 'formateur_id');
    }

    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    public function brief()
    {
        return $this->hasMany(Brief::class, 'formateur_id');
    }

    public function deliverables()
    {
        return $this->hasMany(Deliverable::class, 'student_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'student_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_user');
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

}
