<?php

namespace App\Modules\Report\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportModel extends Model
{
    use HasFactory;

    protected $table = 'daily_reports';

    protected $fillable = [
        'formateur_id',
        'classroom_id',
        'date',
        'absences_count',
        'brief_status',
        'note',
    ];

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }

    public function classroom()
    {
        return $this->belongsTo(\App\Modules\Classroom\Infrastructure\Models\ClassroomModel::class, 'classroom_id');
    }
}
