<?php

namespace App\Modules\Absence\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'duration',
        'status',
        'justification_file'
    ];

    public function student()
    {
        return $this->belongsTo(UserModel::class, 'student_id');
    }
}
