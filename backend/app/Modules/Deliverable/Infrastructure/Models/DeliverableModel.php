<?php

namespace App\Modules\Deliverable\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverableModel extends Model
{
    use HasFactory;

    protected $table = 'deliverables';

    protected $fillable = [
        'link',
        'date_submission',
        'status',
        'student_id',
        'brief_id'
    ];

    public function student()
    {
        return $this->belongsTo(UserModel::class, 'student_id');
    }

    public function brief()
    {
        return $this->belongsTo(BriefModel::class);
    }
}
