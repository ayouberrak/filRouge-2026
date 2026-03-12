<?php

namespace App\Modules\Activity\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'date_start',
        'date_end'
    ];

    public function users()
    {
        return $this->belongsToMany(UserModel::class, 'activity_user');
    }
}
