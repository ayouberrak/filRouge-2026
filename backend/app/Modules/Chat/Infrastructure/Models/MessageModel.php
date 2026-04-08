<?php

namespace App\Modules\Chat\Infrastructure\Models;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Model;
class MessageModel extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'read_by'
    ];

    protected $casts = [
        'read_by' => 'array'
    ];

    public function conversation()
    {
        return $this->belongsTo(ConversationModel::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(UserModel::class, 'sender_id');
    }
}