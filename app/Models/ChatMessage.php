<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'chat_session_id',
        'chat_id',
        'user_id',
        'message',
        'sender',
        'source',
        'response',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
