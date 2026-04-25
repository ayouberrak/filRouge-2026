<?php

namespace App\Modules\Livrable\Infrastructure\Models;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Squad\Infrastructure\Models\SquadModel;
use Illuminate\Database\Eloquent\Model;

class LivrableModel extends Model
{
    protected $table = 'livrables';

    protected $fillable = [
        'brief_id',
        'student_id',
        'squad_id',
        'link',
        'message',
        'status',
        'formateur_id',
        'formateur_message',
    ];

    public function brief()
    {
        return $this->belongsTo(BriefModel::class, 'brief_id');
    }

    public function student()
    {
        return $this->belongsTo(UserModel::class, 'student_id');
    }

    public function squad()
    {
        return $this->belongsTo(SquadModel::class, 'squad_id');
    }

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }
}
