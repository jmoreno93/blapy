<?php

namespace App\Http\Api\V1\Controllers\ChatUsers;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatUser;

class ChatUserListController extends Controller
{
    public function __invoke()
    {
        return ChatUser::all();
    }
}
