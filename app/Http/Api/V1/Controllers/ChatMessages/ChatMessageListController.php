<?php

namespace App\Http\Api\V1\Controllers\ChatMessages;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatMessage;

class ChatMessageListController extends Controller
{
    public function __invoke()
    {
        return ChatMessage::all();
    }
}
