<?php

namespace App\Modules\Brief\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Deliverable\Infrastructure\Models\DeliverableModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BriefModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date_start',
        'date_end',
        'file',
        'formateur_id'
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(ClassroomModel::class, 'brief_classroom');
    }

    public function deliverables()
    {
        return $this->hasMany(DeliverableModel::class);
    }
}
