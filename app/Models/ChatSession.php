<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $fillable = [
        'chat_user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'chat_user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
