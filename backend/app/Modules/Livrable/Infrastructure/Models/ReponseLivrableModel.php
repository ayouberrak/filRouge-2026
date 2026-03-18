<?php

namespace App\Modules\Livrable\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Model;

class ReponseLivrableModel extends Model
{
    protected $table = 'reponse_livrables';

    protected $fillable = [
        'livrable_id',
        'formateur_id',
        'status',
        'message',
    ];

    public function livrable()
    {
        return $this->belongsTo(LivrableModel::class, 'livrable_id');
    }

    public function formateur()
    {
        return $this->belongsTo(UserModel::class, 'formateur_id');
    }
}
