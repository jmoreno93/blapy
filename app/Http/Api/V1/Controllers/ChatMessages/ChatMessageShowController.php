<?php

namespace App\Http\Api\V1\Controllers\ChatMessages;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatMessage;

class ChatMessageShowController extends Controller
{
    public function __invoke(ChatMessage $chatMessage)
    {
        return $chatMessage;
    }
}
