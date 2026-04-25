<?php

namespace App\Modules\Classroom\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomModel extends Model
{
    use HasFactory;
    
    protected $table = 'classrooms';

    protected $fillable = [
        'name',
        'formateur_id'
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function squads()
    {
        return $this->hasMany(SquadModel::class);
    }

    public function students()
    {
        return $this->hasMany(UserModel::class, 'classroom_id');
    }

    public function briefs()
    {
        return $this->belongsToMany(BriefModel::class, 'brief_classroom');
    }
}
