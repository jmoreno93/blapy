<?php

namespace App\Http\Api\V1\Controllers\ChatUsers;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatUser;

class ChatUserShowController extends Controller
{
    public function __invoke(ChatUser $chatUser)
    {
        return $chatUser;
    }
}
