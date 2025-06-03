<?php

namespace App\Http\Api\V1\Controllers\ChatUsers;

use App\Http\Api\V1\Controllers\Controller;
use App\Models\ChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatUserUpdateController extends Controller
{
    public function __invoke(Request $request, ChatUser $chatUser)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'platform' => 'sometimes|string',
            'external_id' => 'sometimes|string|unique:chat_users,external_id,' . $chatUser->id,
            'username' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chatUser->update($validator->validated());

        return response()->json($chatUser);
    }
}
