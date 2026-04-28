<?php

namespace App\Modules\Squad\Infrastructure\Models;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\User\Infrastructure\Models\UserModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SquadModel extends Model
{
    use HasFactory;
    protected $table = 'squads';

    protected $fillable = [
        'name',
        'classroom_id'
    ];

    public function classroom()
    {
        return $this->belongsTo(ClassroomModel::class);
    }

    public function members()
    {
        return $this->hasMany(UserModel::class, 'squad_id');
    }

    public function briefs()
    {
        return $this->belongsToMany(BriefModel::class, 'brief_squad', 'squad_id', 'brief_id');
    }
}
