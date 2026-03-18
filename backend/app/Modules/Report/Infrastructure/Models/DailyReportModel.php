<?php

namespace App\Modules\Report\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'absences_count',
        'brief_status'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class);
    }
}
