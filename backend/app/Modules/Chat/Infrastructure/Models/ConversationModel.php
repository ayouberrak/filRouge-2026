<?php

namespace App\Modules\Chat\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Model;
class ConversationModel extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'type',
        'related_id',
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(UserModel::class, 'conversation_user', 'conversation_id', 'user_id')
                    ->withPivot('joined_at', 'left_at');
    }

    public function messages()
    {
        return $this->hasMany(MessageModel::class, 'conversation_id');
    }
}